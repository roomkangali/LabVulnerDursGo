import graphene
from graphene_sqlalchemy import SQLAlchemyObjectType
from models import db, User
from extensions import db as sqlalchemy_db
from datetime import datetime
import json
import random
import time

def create_schema():
    # Import models here to avoid circular imports
    from graphql_models import SensitiveData, LoginAttempt
    
    # Ensure models are loaded
    from models import User
    from extensions import db
    
    # Types
    class UserType(SQLAlchemyObjectType):
        class Meta:
            model = User
            fields = ('id', 'username', 'email', 'password', 'is_admin', 'api_key')
            interfaces = (graphene.relay.Node,)

    class SensitiveDataType(SQLAlchemyObjectType):
        class Meta:
            model = SensitiveData
            interfaces = (graphene.relay.Node,)


    class LoginAttemptType(SQLAlchemyObjectType):
        class Meta:
            model = LoginAttempt
            interfaces = (graphene.relay.Node,)


    class HeavyQueryResult(graphene.ObjectType):
        message = graphene.String()
        total = graphene.Int()

    class Query(graphene.ObjectType):
        # Standard fields for GraphQL introspection
        node = graphene.relay.Node.Field()
        
        # Vulnerable to SQL Injection
        user = graphene.Field(UserType, id=graphene.String(required=True))
        users = graphene.List(UserType)
        search_users = graphene.List(
            UserType, 
            query=graphene.String(required=True, description="Search query for users")
        )
        
        # Sensitive data exposure
        sensitive_data = graphene.List(
            SensitiveDataType,
            description="Get all sensitive data (No authentication required)"
        )
        
        # Heavy query for rate limiting demo
        heavy_query = graphene.Field(
            HeavyQueryResult,
            limit=graphene.Int(required=False, default_value=1000)
        )
        
        # Login logs without auth
        login_logs = graphene.List(
            LoginAttemptType,
            limit=graphene.Int(required=False, default_value=100),
            description="Get recent login attempts (No authentication required)"
        )
        
        # Resolvers
        def resolve_user(self, info, id):
            # Vulnerable to SQL Injection
            try:
                query = f"SELECT * FROM user WHERE id = '{id}'"
                result = db.session.execute(query).first()
                if not result:
                    return None
                # Convert Row to dict and then to User object
                user = User(**dict(result))
                return user
            except Exception as e:
                print(f"Error in resolve_user: {str(e)}")
                return None
            
        def resolve_users(self, info):
            try:
                return User.query.all()
            except Exception as e:
                print(f"Error in resolve_users: {str(e)}")
                return []
            
        def resolve_search_users(self, info, query):
            # Vulnerable to SQL Injection
            try:
                result = db.session.execute(f"SELECT * FROM user WHERE username LIKE '%{query}%'")
                return [User(**dict(row)) for row in result]
            except Exception as e:
                print(f"Error in resolve_search_users: {str(e)}")
                return []
            
        def resolve_sensitive_data(self, info):
            # No authentication required
            try:
                return SensitiveData.query.all()
            except Exception as e:
                print(f"Error in resolve_sensitive_data: {str(e)}")
                return []
            
        def resolve_heavy_query(self, info, limit=1000):
            # Simulate heavy query
            try:
                time.sleep(2)  # Delay for 2 seconds
                return HeavyQueryResult(
                    message=f"Heavy query executed with limit {limit}",
                    total=random.randint(1000, 10000)
                )
            except Exception as e:
                print(f"Error in resolve_heavy_query: {str(e)}")
                return HeavyQueryResult(message="Error", total=0)
            
        def resolve_login_logs(self, info, limit=100):
            # No authentication required
            try:
                return LoginAttempt.query.order_by(
                    LoginAttempt.timestamp.desc()
                ).limit(limit).all()
            except Exception as e:
                print(f"Error in resolve_login_logs: {str(e)}")
                return []

    class LoginInput(graphene.InputObjectType):
        username = graphene.String(required=True)
        password = graphene.String(required=True)

    class AuthPayload(graphene.ObjectType):
        token = graphene.String()
        user = graphene.Field(UserType)

    class Mutation(graphene.ObjectType):
        # NoSQL Injection vulnerable login
        login = graphene.Field(
            AuthPayload,
            input=LoginInput(required=True, description="Login credentials"),
            ip_address=graphene.String(description="IP address of the client"),
            user_agent=graphene.String(description="User agent string of the client")
        )
        
        # No input validation
        update_profile = graphene.String(
            id=graphene.ID(required=True, description="User ID"),
            data=graphene.String(required=True, description="JSON string with user data to update")
        )
        
        async def resolve_login(self, info, input, ip_address=None, user_agent=None):
            try:
                # Log the login attempt
                attempt = LoginAttempt(
                    username=input.username,
                    ip_address=ip_address or info.context.get('request').remote_addr or "127.0.0.1",
                    user_agent=user_agent or info.context.get('request').user_agent.string or "Unknown"
                )
                db.session.add(attempt)
                db.session.commit()
                
                # NoSQL Injection vulnerable query - intentionally left vulnerable
                query = f"SELECT * FROM user WHERE username = '{input.username}' AND password = '{input.password}'"
                try:
                    result = db.session.execute(query).first()
                    if result:
                        user = User(**dict(result))
                        return AuthPayload(
                            token=f"vulnerable-jwt-token-{user.id}",
                            user=user
                        )
                except Exception as e:
                    print(f"[ERROR] Login query failed: {str(e)}")
                
                return AuthPayload(token=None, user=None)
                
            except Exception as e:
                print(f"[ERROR] Login failed: {str(e)}")
                return AuthPayload(token=None, user=None)
        
        async def resolve_update_profile(self, info, id, data):
            try:
                # No input validation - vulnerable to injection
                user = User.query.get(id)
                if not user:
                    return "Error: User not found"
                
                # Insecure deserialization - intentionally left vulnerable
                try:
                    data_dict = json.loads(data)
                    for key, value in data_dict.items():
                        if hasattr(user, key):
                            setattr(user, key, value)
                    db.session.commit()
                    return "Profile updated successfully"
                except json.JSONDecodeError:
                    return "Error: Invalid JSON data"
                except Exception as e:
                    db.session.rollback()
                    return f"Error updating profile: {str(e)}"
                    
            except Exception as e:
                print(f"[ERROR] Profile update failed: {str(e)}")
                return "Error: Internal server error"

    # Create and return schema
    return graphene.Schema(query=Query, mutation=Mutation)

# This file only contains the create_schema function
