from app import app, db
from graphql_schema import init_graphql_schema

if __name__ == '__main__':
    # Initialize the GraphQL schema
    init_graphql_schema(app)
    
    # Run the Flask app on port 4001
    app.run(debug=True, host='0.0.0.0', port=4001)
