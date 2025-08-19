from flask_sqlalchemy import SQLAlchemy
from flask_jwt_extended import JWTManager
from flask_login import LoginManager
from flask_wtf.csrf import CSRFProtect

# Initialize extensions
db = SQLAlchemy()
jwt = JWTManager()
login_manager = LoginManager()
csrf = CSRFProtect()

def init_extensions(app):
    """Initialize Flask extensions with the given app."""
    # Initialize extensions with the app
    db.init_app(app)
    jwt.init_app(app)
    login_manager.init_app(app)
    csrf.init_app(app)
    
    # Configure login manager
    login_manager.login_view = 'login'  # This is the name of the login view function
    login_manager.login_message_category = 'info'
    
    # Import models after db is initialized
    with app.app_context():
        # Import models here to avoid circular imports
        from models import User, Product, Order, Comment
        from graphql_models import SensitiveData, LoginAttempt
        
        # Create all database tables
        db.create_all()
        
        # This ensures the metadata is properly initialized
        db.reflect()
    
    return db, jwt, login_manager
