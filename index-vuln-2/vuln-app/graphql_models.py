from extensions import db
from datetime import datetime

# This ensures the models are properly registered with SQLAlchemy
metadata = db.Model.metadata

class SensitiveData(db.Model):
    __tablename__ = 'sensitive_data'
    
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(100), nullable=False)
    ssn = db.Column(db.String(20), nullable=False)
    credit_card = db.Column(db.String(20), nullable=False)
    address = db.Column(db.String(200))
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    
    def __repr__(self):
        return f'<SensitiveData {self.name}>'

class LoginAttempt(db.Model):
    __tablename__ = 'login_attempts'
    
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(100))
    timestamp = db.Column(db.DateTime, default=datetime.utcnow)
    ip_address = db.Column(db.String(50))
    user_agent = db.Column(db.String(200))
    
    def __repr__(self):
        return f'<LoginAttempt {self.username} at {self.timestamp}>'
