import graphene
from graphene_sqlalchemy import SQLAlchemyObjectType, SQLAlchemyConnectionField
from flask_jwt_extended import get_jwt_identity, jwt_required
from flask import current_app
from sqlalchemy.orm import configure_mappers

# We'll create a function that will be called after the app is initialized
def create_graphql_schema():
    """Create and return the GraphQL schema after the app is fully initialized."""
    from extensions import db
    from models import User, Product, Order, Comment
    
    # Make sure all models are properly configured
    configure_mappers()
    
    # Define model types
    class UserType(SQLAlchemyObjectType):
        class Meta:
            model = User
            interfaces = (graphene.relay.Node,)
    
    class ProductType(SQLAlchemyObjectType):
        class Meta:
            model = Product
            interfaces = (graphene.relay.Node,)
    
    class OrderType(SQLAlchemyObjectType):
        class Meta:
            model = Order
            interfaces = (graphene.relay.Node,)
    
    class CommentType(SQLAlchemyObjectType):
        class Meta:
            model = Comment
            interfaces = (graphene.relay.Node,)
    
    # Queries
    class Query(graphene.ObjectType):
        node = graphene.relay.Node.Field()
        users = SQLAlchemyConnectionField(UserType)
        products = SQLAlchemyConnectionField(ProductType)
        orders = SQLAlchemyConnectionField(OrderType)
        comments = SQLAlchemyConnectionField(CommentType)
        
        # Secure: Requires authentication
        my_orders = graphene.List(OrderType)
        
        @jwt_required()
        def resolve_my_orders(self, info):
            user_id = get_jwt_identity()
            return Order.query.filter_by(user_id=user_id).all()
    
    # Mutations
    class CreateUser(graphene.Mutation):
        class Arguments:
            username = graphene.String(required=True)
            email = graphene.String(required=True)
            password = graphene.String(required=True)

        user = graphene.Field(UserType)

        def mutate(self, info, username, email, password):
            user = User(username=username, email=email)
            user.set_password(password)
            db.session.add(user)
            db.session.commit()
            return CreateUser(user=user)
    
    class Mutation(graphene.ObjectType):
        create_user = CreateUser.Field()
    
    # Create and return schema
    return graphene.Schema(query=Query, mutation=Mutation)

# This will be set after the app is fully initialized
schema = None

def init_graphql_schema(app):
    """Initialize the GraphQL schema with the app context."""
    global schema
    
    # Only initialize once
    if hasattr(app, 'graphql_initialized') and app.graphql_initialized:
        return
    
    with app.app_context():
        # Import db here to avoid circular imports
        from extensions import db
        
        # Create schema
        schema = create_graphql_schema()
        
        # Add GraphQL endpoint
        from flask_graphql import GraphQLView
        from flask_cors import CORS
        from flask import jsonify, request
        from graphql import graphql
        
        # Enable CORS for all routes
        CORS(app)
        
        # Simple endpoint that returns GraphiQL HTML
        @app.route('/graphiql')
        def graphiql():
            return '''
            <!DOCTYPE html>
            <html>
            <head>
                <title>GraphiQL</title>
                <link href="https://unpkg.com/graphiql/graphiql.min.css" rel="stylesheet" />
            </head>
            <body style="margin: 0;">
                <div id="graphiql" style="height: 100vh;"></div>
                <script src="https://unpkg.com/react/umd/react.production.min.js"></script>
                <script src="https://unpkg.com/react-dom/umd/react-dom.production.min.js"></script>
                <script src="https://unpkg.com/graphiql/graphiql.min.js"></script>
                <script>
                    const fetcher = GraphiQL.createFetcher({
                        url: '/graphql',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                    });
                    
                    ReactDOM.render(
                        React.createElement(GraphiQL, { 
                            fetcher: fetcher,
                            defaultEditorToolsVisibility: true,
                            shouldPersistHeaders: true,
                            headerEditorEnabled: true,
                        }),
                        document.getElementById('graphiql')
                    );
                </script>
            </body>
            </html>
            '''
        
        # Main GraphQL endpoint
        @app.route('/graphql', methods=['GET', 'POST', 'OPTIONS'])
        def graphql_server():
            # Handle OPTIONS for CORS preflight
            if request.method == 'OPTIONS':
                response = jsonify({'data': 'OK'})
                response.headers.add('Access-Control-Allow-Origin', '*')
                response.headers.add('Access-Control-Allow-Headers', 'Content-Type')
                response.headers.add('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                return response
                
            # Handle GET requests (return GraphiQL)
            if request.method == 'GET':
                return '''
                <!DOCTYPE html>
                <html>
                <head>
                    <title>GraphiQL</title>
                    <link href="https://unpkg.com/graphiql/graphiql.min.css" rel="stylesheet" />
                </head>
                <body style="margin: 0;">
                    <div id="graphiql" style="height: 100vh;"></div>
                    <script src="https://unpkg.com/react/umd/react.production.min.js"></script>
                    <script src="https://unpkg.com/react-dom/umd/react-dom.production.min.js"></script>
                    <script src="https://unpkg.com/graphiql/graphiql.min.js"></script>
                    <script>
                        const fetcher = GraphiQL.createFetcher({
                            url: '/graphql',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                        });
                        
                        ReactDOM.render(
                            React.createElement(GraphiQL, { 
                                fetcher: fetcher,
                                defaultEditorToolsVisibility: true,
                                shouldPersistHeaders: true,
                                headerEditorEnabled: true,
                            }),
                            document.getElementById('graphiql')
                        );
                    </script>
                </body>
                </html>
                '''
            
            # Handle POST requests
            try:
                # Support both application/json and application/graphql content types
                if request.content_type == 'application/graphql':
                    data = {'query': request.data.decode('utf-8')}
                else:
                    data = request.get_json()
                
                if not data:
                    return jsonify({
                        'errors': [{'message': 'No query data provided'}] 
                    }), 400
                
                query = data.get('query')
                variables = data.get('variables')
                operation_name = data.get('operationName')
                
                if not query:
                    return jsonify({
                        'errors': [{'message': 'No query provided'}] 
                    }), 400
                
                # Execute the query
                result = graphql(
                    schema=schema,
                    source=query,
                    variable_values=variables,
                    operation_name=operation_name,
                    context_value={'session': db.session, 'request': request}
                )
                
                response = jsonify({
                    'data': result.data,
                    'errors': [{'message': str(e)} for e in result.errors] if result.errors else None
                })
                
                # Add CORS headers
                response.headers.add('Access-Control-Allow-Origin', '*')
                response.headers.add('Access-Control-Allow-Headers', 'Content-Type')
                
                return response
                
            except Exception as e:
                return jsonify({
                    'errors': [{'message': str(e)}]
                }), 500
        
        # Mark as initialized
        app.graphql_initialized = True
