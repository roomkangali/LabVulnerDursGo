from app import app
from extensions import db
from models import User, Product, Order, Comment
from graphql_models import SensitiveData, LoginAttempt
from werkzeug.security import generate_password_hash
from datetime import datetime, timedelta

# Pastikan semua model diimpor sebelum create_all()
# Ini memastikan SQLAlchemy mengetahui semua tabel yang perlu dibuat

def init_db():
    with app.app_context():
        # Pastikan semua model terdaftar dengan impor yang spesifik
        from models import User, Product, Order, Comment
        from graphql_models import SensitiveData, LoginAttempt
        
        # Hapus semua tabel yang ada
        db.drop_all()
        
        # Buat semua tabel database
        db.create_all()
        
        # Add sample data if tables are empty       
        # Create admin user
        admin = User(
            username='admin',
            email='admin@vulnshop.com',
            is_admin=True
        )
        admin.set_password('admin123')
        
        # Create regular user
        user1 = User(
            username='user1',
            email='user1@example.com',
            is_admin=False
        )
        user1.set_password('password123')
        
        # Add users to session
        db.session.add(admin)
        db.session.add(user1)
        
        # Add sensitive data
        sensitive_data = [
            SensitiveData(
                name='John Doe',
                ssn='123-45-6789',
                credit_card='4111111111111111',
                address='123 Main St, Anytown, USA'
            ),
            SensitiveData(
                name='Jane Smith',
                ssn='987-65-4321',
                credit_card='5555555555554444',
                address='456 Oak St, Somewhere, USA'
            )
        ]
        db.session.add_all(sensitive_data)
        
        # Add login attempts
        login_attempts = [
            LoginAttempt(
                username='admin',
                ip_address='192.168.1.1',
                user_agent='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                timestamp=datetime.utcnow() - timedelta(hours=2)
            ),
            LoginAttempt(
                username='user1',
                ip_address='10.0.0.1',
                user_agent='Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
                timestamp=datetime.utcnow() - timedelta(hours=1)
            )
        ]
        db.session.add_all(login_attempts)
        
        # Create sample products
        products = [
            Product(
                name='Smartphone X',
                description='Latest smartphone with AI camera and edge-to-edge display.',
                price=999.99,
                created_by=1,
                image='smartphone.jpg'
            ),
            Product(
                name='Laptop Pro',
                description='Powerful laptop for professionals with 16GB RAM and 1TB SSD.',
                price=1499.99,
                created_by=1,
                image='laptop.jpg'
            ),
            Product(
                name='Wireless Earbuds',
                description='Noise cancelling wireless earbuds with 30h battery life.',
                price=199.99,
                created_by=1,
                image='earbuds.jpg'
            ),
            Product(
                name='Smart Watch',
                description='Fitness tracker with heart rate monitor and GPS.',
                price=249.99,
                created_by=1,
                image='watch.jpg'
            ),
            Product(
                name='4K Smart TV',
                description='55" 4K Ultra HD Smart LED TV with HDR.',
                price=699.99,
                created_by=1,
                image='tv.jpg'
            )
        ]
        
        # Add products to session
        for product in products:
            db.session.add(product)
        
        # Create sample orders
        orders = [
            Order(
                user_id=2,  # user1
                product_id=1,  # Smartphone X
                quantity=1,
                status='delivered',
                created_at=datetime.utcnow() - timedelta(days=10)
            ),
            Order(
                user_id=2,  # user1
                product_id=3,  # Wireless Earbuds
                quantity=2,
                status='shipped',
                created_at=datetime.utcnow() - timedelta(days=5)
            )
        ]
        
        # Add orders to session
        for order in orders:
            db.session.add(order)
        
        # Create sample comments (with potential XSS payloads)
        comments = [
            Comment(
                content='Great product! Works perfectly.',
                user_id=2,  # user1
                product_id=1,  # Smartphone X
                created_at=datetime.utcnow() - timedelta(days=8)
            ),
            Comment(
                content='<script>alert("XSS Test")</script> This is a test comment with XSS payload.',
                user_id=2,  # user1
                product_id=2,  # Laptop Pro
                created_at=datetime.utcnow() - timedelta(days=3)
            ),
            Comment(
                content='<img src="x" onerror=\'alert(\'DOM XSS\')\'> Check out this image!',
                user_id=1,  # admin
                product_id=3,  # Wireless Earbuds
                created_at=datetime.utcnow() - timedelta(days=1)
            )
        ]
        
        # Add comments to session
        for comment in comments:
            db.session.add(comment)
        
        # Commit all changes
        db.session.commit()
        
        print("Database initialized successfully!")
        print("Admin user created:")
        print(f"Username: admin")
        print(f"Password: admin123")
        print("\nRegular user created:")
        print(f"Username: user1")
        print(f"Password: password123")

if __name__ == '__main__':
    init_db()
