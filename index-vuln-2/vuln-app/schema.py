import graphene
from graphene_sqlalchemy import SQLAlchemyObjectType, SQLAlchemyConnectionField
from flask_jwt_extended import get_jwt_identity, jwt_required

# Import db first to ensure models are registered
from extensions import db

def create_schema():
    # Import models inside the function to avoid circular imports
    from models import User, Product, Order, Comment
    
    # Define model types
    class UserType(SQLAlchemyObjectType):
        class Meta:
            model = User
            interfaces = (graphene.relay.Node,)
            batching = True

    class ProductType(SQLAlchemyObjectType):
        class Meta:
            model = Product
            interfaces = (graphene.relay.Node,)
            batching = True

    class OrderType(SQLAlchemyObjectType):
        class Meta:
            model = Order
            interfaces = (graphene.relay.Node,)
            batching = True

    class CommentType(SQLAlchemyObjectType):
        class Meta:
            model = Comment
            interfaces = (graphene.relay.Node,)
            batching = True
    
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

# Create schema instance
schema = create_schema()
