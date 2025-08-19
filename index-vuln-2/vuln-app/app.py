import os
import jwt
import uuid
import pickle
import base64
import sqlite3
from datetime import datetime, timedelta
from dateutil.relativedelta import relativedelta
from functools import wraps
from flask import Flask, request, jsonify, render_template, send_from_directory, redirect, url_for, flash, session, send_file
from flask_jwt_extended import create_access_token, jwt_required, get_jwt_identity
from flask_graphql import GraphQLView
from flask_login import current_user, login_user, logout_user, login_required, UserMixin
from flask_wtf import FlaskForm
from flask_wtf.csrf import CSRFProtect, generate_csrf, CSRFError
from wtforms import StringField, TextAreaField, IntegerField, validators, FileField
from werkzeug.security import generate_password_hash, check_password_hash
from werkzeug.utils import secure_filename
from functools import wraps

def csrf_exempt(f):
    """Decorator to mark a route as CSRF exempt"""
    f.csrf_exempt = True
    return f

def admin_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if not current_user.is_authenticated or not current_user.is_admin:
            flash('Access denied. Admin privileges required.', 'danger')
            return redirect(url_for('index'))
        return f(*args, **kwargs)
    return decorated_function

def validate_password(password):
    """Validates password against security policy"""
    if len(password) < 8:
        return False, f'Password must be at least 8 characters long'
    if len(password) > 128:
        return False, f'Password must be at most 128 characters long'
    if not re.search(r'[A-Z]', password):
        return False, 'Password must contain at least one uppercase letter'
    if not re.search(r'[a-z]', password):
        return False, 'Password must contain at least one lowercase letter'
    if not re.search(r'[0-9]', password):
        return False, 'Password must contain at least one number'
    if not re.search(r'[^A-Za-z0-9]', password):
        return False, 'Password must contain at least one special character'
    return True, ''

from config import Config
from extensions import db, jwt, login_manager, init_extensions
from graphql_schema import init_graphql_schema

# Initialize Flask app
app = Flask(__name__)
app.config.from_object(Config)

# Initialize CSRF Protection
csrf = CSRFProtect(app)

# Initialize extensions and database
db, jwt, login_manager = init_extensions(app)

# Form for comments
class CommentForm(FlaskForm):
    content = TextAreaField('Comment', validators=[validators.DataRequired()])
    rating = IntegerField('Rating', validators=[
        validators.NumberRange(min=1, max=5),
        validators.DataRequired()
    ], default=5)

# Add CSRF token and form to all templates
@app.context_processor
def inject_vars():
    return dict(
        csrf_token=generate_csrf,
        form=CommentForm()
    )

# Ensure upload folder exists
os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)

# Initialize GraphQL schema after database is ready
from graphql_schema import init_graphql_schema
init_graphql_schema(app)

# Custom filters
def time_ago(dt):
    now = datetime.utcnow()
    diff = relativedelta(now, dt)
    
    if diff.years > 0:
        return f"{diff.years} years ago"
    elif diff.months > 0:
        return f"{diff.months} months ago"
    elif diff.days > 0:
        return f"{diff.days} days ago"
    elif diff.hours > 0:
        return f"{diff.hours} hours ago"
    elif diff.minutes > 0:
        return f"{diff.minutes} minutes ago"
    else:
        return "just now"

def truncate(s, length=200, end='...'):
    """Truncate a string to the specified length and add an ellipsis if truncated."""
    if len(s) <= length:
        return s
    return s[:length - len(end)].strip() + end

# Custom filters and other configurations are now after app initialization

# Add custom filters to Jinja2 environment
app.jinja_env.filters['time_ago'] = time_ago
app.jinja_env.filters['truncate'] = truncate

# Function to create initial data
@app.cli.command('init-db')
def init_db():
    """Initialize the database with initial data."""
    from models import User, Product, Order, Comment
    
    with app.app_context():
        try:
            # Drop and recreate all tables
            db.drop_all()
            db.create_all()
            
            # Create admin user
            admin = User(
                username='admin', 
                email='admin@vulnshop.com', 
                is_admin=True,
                preferences='',
                profile_picture='default.png'
            )
            admin.set_password('admin123')
            db.session.add(admin)
            
            # Create regular users
            test_user = User(
                username='test', 
                email='test@example.com', 
                is_admin=False,
                preferences='',
                profile_picture='default.png'
            )
            test_user.set_password('test123')
            db.session.add(test_user)
            
            user1 = User(
                username='user1', 
                email='user1@example.com', 
                is_admin=False,
                preferences='',
                profile_picture='default.png'
            )
            user1.set_password('password123')
            db.session.add(user1)
            
            # Create sample products
            products = [
                Product(
                    name='Vulnerable Web App',
                    description='A deliberately vulnerable web application for security testing',
                    price=0.0,
                    image='vulnapp.jpg',
                    created_by=1  # admin user
                ),
                Product(
                    name='Security Testing Guide',
                    description='Comprehensive guide to web application security testing',
                    price=29.99,
                    image='guide.jpg',
                    created_by=1
                ),
                Product(
                    name='Pentesting Toolkit',
                    description='Collection of tools for penetration testing',
                    price=49.99,
                    image='toolkit.jpg',
                    created_by=1
                )
            ]
            db.session.add_all(products)
            
            # Create sample comments
            comments = [
                Comment(
                    content='Great product!',
                    rating=5,
                    user_id=2,  # test user
                    product_id=1
                ),
                Comment(
                    content='Very useful for learning security',
                    rating=4,
                    user_id=3,  # user1
                    product_id=2
                )
            ]
            db.session.add_all(comments)
            
            # Commit all changes
            db.session.commit()
            print('Database initialized successfully!')
            print('Admin user created:')
            print('  Username: admin')
            print('  Password: admin123')
            print('\nRegular users created:')
            print('  Username: test')
            print('  Password: test123')
            print('  Username: user1')
            print('  Password: password123')
            print('\nSample products and comments have been added to the database.')
        except Exception as e:
            db.session.rollback()
            print(f'Error initializing database: {str(e)}')
            raise

# User loader function
@login_manager.user_loader
def load_user(user_id):
    from models import User
    return User.query.get(int(user_id))

# Context processor to make current_user available in all templates
@app.context_processor
def inject_user():
    return dict(current_user=current_user)

# Sample data is now initialized using the init-db command

# Routes
# XSS Test Endpoints

# Reflected XSS Demo
@app.route('/xss-demo')
def xss_demo():
    search = request.args.get('search', '')
    return f'''
    <!DOCTYPE html>
    <html>
    <head>
        <title>Reflected XSS Demo</title>
        <style>
            .vulnerable {{ border: 1px solid #ff6b6b; padding: 10px; margin: 10px 0; }}
            .payload {{ background: #f8f9fa; padding: 5px; border-radius: 3px; }}
        </style>
    </head>
    <body>
        <div style="max-width: 800px; margin: 0 auto; padding: 20px;">
            <h1>Reflected XSS Demo</h1>
            <p>Search results for: {search}</p>
            
            <div class="vulnerable">
                <h3>Vulnerable Output (Reflected XSS):</h3>
                {search}
            </div>
            
            <h3>Test Payloads:</h3>
            <ul>
                <li><span class="payload">&lt;script&gt;alert('Reflected XSS')&lt;/script&gt;</span></li>
                <li><span class="payload">&lt;img src="x" onerror="alert('XSS')"&gt;</span></li>
                <li><span class="payload">&lt;svg onload="alert('XSS')"&gt;&lt;/svg&gt;</span></li>
            </ul>
            
            <form>
                <input type="text" name="search" value="{search}" style="width: 300px; padding: 5px;">
                <button type="submit">Test</button>
            </form>
            
            <p><a href="/xss-test">Go to DOM-based XSS Test</a> | <a href="/">Back to Home</a></p>
        </div>
    </body>
    </html>
    '''

# DOM-based XSS Test
@app.route('/xss-test')
def xss_test():
    user_input = request.args.get('input', '')
    return f"""
    <!DOCTYPE html>
    <html>
    <head>
        <title>DOM-based XSS Test</title>
        <style>
            body {{ font-family: Arial, sans-serif; line-height: 1.6; }}
            .container {{ max-width: 800px; margin: 0 auto; padding: 20px; }}
            .vulnerable {{ border: 1px solid #ff6b6b; padding: 15px; margin: 20px 0; }}
            .payload {{ 
                background: #f8f9fa; 
                padding: 8px 12px; 
                border-radius: 4px; 
                font-family: monospace;
                display: inline-block;
                margin: 5px 0;
            }}
            input[type="text"] {{ 
                padding: 8px; 
                width: 300px; 
                margin-right: 10px;
            }}
            button {{ 
                padding: 8px 16px; 
                background: #4CAF50; 
                color: white; 
                border: none; 
                border-radius: 4px; 
                cursor: pointer;
            }}
            button:hover {{ background: #45a049; }}
            .example {{ 
                background: #f0f8ff; 
                padding: 15px; 
                border-radius: 5px; 
                margin: 15px 0;
            }}
        </style>
    </head>
    <body>
        <div class="container">
            <h1>DOM-based XSS Test Area</h1>
            <p>This page demonstrates a DOM-based XSS vulnerability. The vulnerability occurs when user input is directly used in the DOM without proper sanitization.</p>
            
            <div class="vulnerable">
                <h3>Vulnerable Input:</h3>
                <div>
                    <input type="text" id="userInput" value="{user_input}" placeholder="Enter payload here">
                    <button onclick="updateContent()">Update Content</button>
                </div>
                
                <h3>Output:</h3>
                <div id="output">
                    {f'You entered: {user_input}' if user_input else 'Enter text and click Update to see the result'}
                </div>
                
                <script>
                // Vulnerable code - directly using user input in innerHTML
                function updateContent() {{
                    const input = document.getElementById('userInput').value;
                    // This is the vulnerable line - user input is directly inserted into innerHTML
                    document.getElementById('output').innerHTML = 'You entered: ' + input;
                    
                    // Update URL without page reload
                    const url = new URL(window.location);
                    url.searchParams.set('input', input);
                    window.history.pushState({{}}, '', url);
                }}
                
                // Update content on page load if there's an input in the URL
                document.addEventListener('DOMContentLoaded', function() {{
                    const urlParams = new URLSearchParams(window.location.search);
                    const input = urlParams.get('input');
                    if (input) {{
                        document.getElementById('userInput').value = input;
                        document.getElementById('output').innerHTML = 'You entered: ' + input;
                    }}
                }});
                </script>
            </div>
            
            <div class="example">
                <h3>Test Payloads (DOM-based XSS):</h3>
                <p>Try these payloads in the input field above:</p>
                <ul>
                    <li><span class="payload">&lt;img src=x onerror=alert('DOM XSS')&gt;</span></li>
                    <li><span class="payload">&lt;svg onload=alert('XSS')&gt;&lt;/svg&gt;</span></li>
                    <li><span class="payload">&lt;div onmouseover="alert('XSS')"&gt;Hover over me&lt;/div&gt;</span></li>
                </ul>
                
                <h3>How It Works:</h3>
                <p>This vulnerability occurs because the application takes user input and directly inserts it into the page using <code>innerHTML</code> without proper sanitization. This allows an attacker to inject arbitrary JavaScript code that will be executed in the context of the page.</p>
            </div>
            
            <p><a href="/xss-demo">Go to Reflected XSS Test</a> | <a href="/">Back to Home</a></p>
        </div>
    </body>
    </html>
    """

# Main index route
@app.route('/')
def index():
    from models import Product
    search = request.args.get('search', '')
    
    # Very simple SQL Injection vulnerability
    try:
        if search:
            # Direct string concatenation - intentionally vulnerable
            query = f"SELECT * FROM products WHERE name = '{search}'"
            app.logger.info(f"Executing query: {query}")
            
            # Execute with error
            conn = sqlite3.connect('instance/vuln_shop.db')
            cursor = conn.cursor()
            
            try:
                cursor.execute(query)
                rows = cursor.fetchall()
                
                # If no error, process results
                products = []
                for row in rows:
                    product = Product()
                    for i, col in enumerate(cursor.description):
                        setattr(product, col[0], row[i])
                    products.append(product)
                        
            except sqlite3.Error as e:
                # Show SQL error directly for demonstration
                return f"""
                <h1>SQL Error</h1>
                <p><strong>Error:</strong> {str(e)}</p>
                <p><strong>Query:</strong> {query}</p>
                <p><a href="/">Back to search</a></p>
                """, 500
                
            finally:
                conn.close()
        else:
            products = Product.query.all()
            
        return render_template('index.html', products=products, search=search)
        
    except Exception as e:
        app.logger.error(f"Error in search: {str(e)}")
        flash('An error occurred during search', 'error')
        return render_template('index.html', products=[], search=search)

@app.errorhandler(404)
def not_found_error(error):
    return render_template('errors/404.html'), 404

@app.errorhandler(CSRFError)
def handle_csrf_error(error):
    # If it's an API request, return JSON
    if request.path.startswith('/api/') or request.path == '/graphql':
        return jsonify({'error': 'CSRF token missing or invalid'}), 403
    # Otherwise, redirect to login with flash message
    flash('Session expired. Please log in again.', 'danger')
    return redirect(url_for('login'))

@app.route('/product/<int:product_id>', methods=['GET'])
def product_detail(product_id):
    from models import Product, Comment, User
    
    product = Product.query.get_or_404(product_id)
    
    # Get comments with user info
    comments = db.session.query(Comment, User).join(
        User, Comment.user_id == User.id
    ).filter(
        Comment.product_id == product_id
    ).order_by(
        Comment.created_at.desc()
    ).all()
    
    # Prepare comments data for template
    comments_data = [{
        'id': comment.id,
        'content': comment.content,
        'rating': comment.rating,
        'created_at': comment.created_at,
        'user': {
            'id': user.id,
            'username': user.username,
            'avatar': user.username[0].upper()  # First letter as avatar
        }
    } for comment, user in comments]
    
    return render_template('product.html', 
                         product=product, 
                         comments=comments_data,
                         form=CommentForm())

@app.route('/profile/<int:user_id>')
@login_required
def user_profile(user_id):
    from models import User, Order, Product, Comment  # Import models here to avoid circular imports
    
    # Intentionally vulnerable to IDOR - no access control check
    user = User.query.get_or_404(user_id)
    
    try:
        # Get user's recent orders with product info
        recent_orders = db.session.query(Order, Product.name, Product.price, Product.image).join(
            Product, Order.product_id == Product.id
        ).filter(
            Order.user_id == user_id
        ).order_by(
            Order.created_at.desc()
        ).limit(5).all()
        
        # Format orders data as dictionaries for template
        formatted_orders = [{
            'id': order.id,
            'status': order.status,
            'created_at': order.created_at,
            'product': {
                'name': name,
                'price': price,
                'image': image
            }
        } for order, name, price, image in recent_orders]
        
        # Get user's recent comments with product and user info
        recent_comments = db.session.query(
            Comment, 
            Product.name.label('product_name'),
            User.username
        ).join(
            Product, Comment.product_id == Product.id
        ).join(
            User, Comment.user_id == User.id
        ).filter(
            Comment.user_id == user_id
        ).order_by(
            Comment.created_at.desc()
        ).limit(5).all()
        
        # Format comments data as dictionaries for template
        formatted_comments = [{
            'id': comment.id,
            'content': comment.content,
            'created_at': comment.created_at,
            'username': username,  # Flat structure for template
            'product': {
                'name': product_name
            }
        } for comment, product_name, username in recent_comments]
        
    except Exception as e:
        print(f"Error fetching profile data: {e}")
        formatted_orders = []
        formatted_comments = []
    
    return render_template('profile.html', 
                         user=user, 
                         recent_orders=formatted_orders,
                         recent_comments=formatted_comments)

@app.route('/api/products')
def api_products():
    from models import Product
    products = Product.query.all()
    return jsonify([{
        'id': p.id,
        'name': p.name,
        'description': p.description,
        'price': p.price,
        'image': p.image
    } for p in products])

@app.route('/product/<int:product_id>/comment', methods=['POST'])
@login_required
def add_comment(product_id):
    from models import Comment, Product, User
    
    # Check if product exists
    product = Product.query.get_or_404(product_id)
    
    # Get form data
    content = request.form.get('content')
    rating = request.form.get('rating', 5, type=int)
    
    if not content:
        flash('Comment content is required', 'error')
        return redirect(url_for('product_detail', product_id=product_id))
    
    # Create comment
    comment = Comment(
        content=content,
        rating=rating,
        user_id=current_user.id,
        product_id=product_id
    )
    
    db.session.add(comment)
    db.session.commit()
    
    flash('Your review has been submitted!', 'success')
    return redirect(url_for('product_detail', product_id=product_id))

# API endpoint for getting product comments
@app.route('/api/products/<int:product_id>/comments')
def get_product_comments(product_id):
    from models import Comment, Product, User
    
    # Check if product exists
    if not Product.query.get(product_id):
        return jsonify({'error': 'Product not found'}), 404
    
    comments = Comment.query.filter_by(product_id=product_id)\
        .join(User)\
        .order_by(Comment.created_at.desc())\
        .all()
    
    return jsonify([{
        'id': c.id,
        'content': c.content,
        'created_at': c.created_at.isoformat(),
        'user': {
            'id': c.user.id,
            'username': c.user.username
        }
    } for c in comments])

@app.route('/upload', methods=['GET', 'POST'])
@login_required
def upload_file():
    if request.method == 'POST':
        # Check CSRF token
        if not validate_csrf(request.form.get('_csrf_token', '')):
            flash('CSRF token missing or invalid', 'danger')
            return redirect(url_for('index'))
            
        # Check if the post request has the file part
        if 'file' not in request.files:
            flash('No file part', 'danger')
            return redirect(request.url)
            
        file = request.files['file']
        
        # If user does not select file, browser also
        # submit an empty part without filename
        if file.filename == '':
            flash('No selected file', 'danger')
            return redirect(request.url)
            
        if file:
            try:
                # Secure the filename and create a unique name to prevent overwrites
                filename = secure_filename(file.filename)
                unique_filename = f"{uuid.uuid4().hex}_{filename}"
                
                # Ensure the upload folder exists
                os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)
                filepath = os.path.join(app.config['UPLOAD_FOLDER'], unique_filename)
                
                # Save the file
                file.save(filepath)
                
                # Set proper permissions (read/write for owner, read for others)
                os.chmod(filepath, 0o644)
                
                flash('File successfully uploaded', 'success')
                return redirect(url_for('index'))
                
            except Exception as e:
                app.logger.error(f'Error uploading file: {str(e)}')
                flash('An error occurred while uploading the file', 'danger')
                return redirect(request.url)
    
    # Show upload form with CSRF token
    return f'''
    <!doctype html>
    <title>Upload new File</title>
    <h1>Upload new File</h1>
    <form method=post enctype=multipart/form-data>
      <input type="hidden" name="_csrf_token" value="{generate_csrf()}">
      <input type=file name=file>
      <input type=submit value=Upload>
    </form>
    <p><a href="{url_for('index')}">Back to Home</a></p>
    '''

# Auth routes
def create_access_token(user_id, is_admin=False):
    expires = datetime.utcnow() + timedelta(hours=1)
    return jwt.encode(
        {'user_id': user_id, 'is_admin': is_admin, 'exp': expires},
        app.config['SECRET_KEY'],
        algorithm='HS256'
    )

@app.route('/api/login', methods=['POST'])
def api_login():
    from models import User
    
    data = request.get_json()
    username = data.get('username')
    password = data.get('password')
    
    if not username or not password:
        return jsonify({'error': 'Username and password required'}), 400
    
    user = User.query.filter_by(username=username).first()
    if not user or not user.check_password(password):
        return jsonify({'error': 'Invalid username or password'}), 401
    
    access_token = create_access_token(user.id, user.is_admin)
    return jsonify({
        'access_token': access_token,
        'user_id': user.id,
        'username': user.username,
        'is_admin': user.is_admin
    })

@app.route('/login', methods=['GET', 'POST'])
def login():
    from models import User
    
    if current_user.is_authenticated:
        return redirect(url_for('index'))
    
    if request.method == 'POST':
        username = request.form.get('username')
        password = request.form.get('password')
        
        if not username or not password:
            flash('Please provide both username and password', 'danger')
            return redirect(url_for('login'))
        
        user = User.query.filter_by(username=username).first()
        
        # Intentionally vulnerable to brute force - no rate limiting
        if user and user.check_password(password):
            login_user(user)
            next_page = request.args.get('next')
            flash('You have been logged in!', 'success')
            
            # Jika request JSON (API)
            if request.is_json:
                access_token = create_access_token(user.id, user.is_admin)
                return jsonify({
                    'access_token': access_token,
                    'user_id': user.id,
                    'username': user.username,
                    'is_admin': user.is_admin
                })
                
            return redirect(next_page or url_for('index'))
            
        # Intentionally reveal if username exists
        if user:
            flash('Invalid password', 'danger')
        else:
            flash('User not found', 'danger')
    
    return render_template('login.html')

@app.route('/logout', methods=['GET', 'POST'])
@login_required
def logout():
    if request.method == 'POST':
        logout_user()
        flash('You have been logged out.', 'info')
        return redirect(url_for('login'))
    # If accessed via GET, show a confirmation page
    return render_template('logout.html')

# ===== VULNERABLE: Local File Inclusion =====
@app.route('/view')
def view_file():
    """Vulnerable to Local File Inclusion"""
    file_path = request.args.get('file', '')  # No path sanitization
    try:
        with open(file_path, 'r') as f:
            content = f.read()
        return f'<pre>{content}</pre>'
    except Exception as e:
        return f'Error reading file: {str(e)}', 400

# ===== VULNERABLE: Insecure Deserialization =====
@app.route('/preferences', methods=['GET', 'POST'])
@login_required
def user_preferences():
    """Vulnerable to Insecure Deserialization"""
    if request.method == 'POST':
        try:
            # Insecure deserialization of user preferences
            prefs = request.form.get('prefs', '')
            if prefs:
                # Decode base64 and deserialize
                decoded = base64.b64decode(prefs)
                current_user.preferences = pickle.loads(decoded)
                db.session.commit()
                flash('Preferences updated!', 'success')
        except Exception as e:
            flash(f'Error updating preferences: {str(e)}', 'danger')
        return redirect(url_for('user_profile', user_id=current_user.id))
    
    # Show preferences form
    return '''
        <h2>Update Preferences</h2>
        <p>Current preferences: {}</p>
        <form method="POST">
            <input type="hidden" name="_csrf_token" value="{}">
            <textarea name="prefs" rows="5" cols="50"></textarea><br>
            <button type="submit">Update Preferences</button>
        </form>
        <p>Example (base64 encoded): {}</p>
    '''.format(
        current_user.preferences,
        generate_csrf(),
        base64.b64encode(pickle.dumps({'theme': 'dark', 'notifications': True})).decode()
    )

# ===== VULNERABLE: File Upload =====
UPLOAD_FOLDER = os.path.join('static', 'uploads')
os.makedirs(UPLOAD_FOLDER, exist_ok=True)

ALLOWED_EXTENSIONS = {'png', 'jpg', 'jpeg', 'gif'}

def allowed_file(filename):
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS

@app.route('/profile/upload', methods=['POST'])
@login_required
def upload_profile_picture():
    """Vulnerable File Upload"""
    if 'file' not in request.files:
        flash('No file part', 'danger')
        return redirect(url_for('user_profile', user_id=current_user.id))
    
    file = request.files['file']
    if file.filename == '':
        flash('No selected file', 'danger')
        return redirect(url_for('user_profile', user_id=current_user.id))
    
    if file and allowed_file(file.filename):
        try:
            # Vulnerable: Using original filename which can lead to path traversal
            filename = secure_filename(file.filename)
            
            # Create uploads directory if not exists
            os.makedirs(UPLOAD_FOLDER, exist_ok=True)
            
            # Save file
            filepath = os.path.join(UPLOAD_FOLDER, filename)
            file.save(filepath)
            
            # Update user's profile picture
            current_user.profile_picture = filename
            db.session.commit()
            
            flash('Profile picture updated!', 'success')
        except Exception as e:
            flash(f'Error uploading file: {str(e)}', 'danger')
    else:
        flash('Invalid file type. Allowed types are: ' + ', '.join(ALLOWED_EXTENSIONS), 'danger')
    
    return redirect(url_for('user_profile', user_id=current_user.id))

@app.route('/admin')
@login_required
def admin_dashboard():
    # Intentionally vulnerable to Broken Access Control
    # No admin role check
    from models import User, Product, Order, Comment
    
    stats = {
        'users': User.query.count(),
        'products': Product.query.count(),
        'orders': Order.query.count(),
        'comments': Comment.query.count()
    }
    
    recent_users = User.query.order_by(User.created_at.desc()).limit(5).all()
    recent_orders = db.session.query(Order, Product.name, User.username)\
        .join(Product, Order.product_id == Product.id)\
        .join(User, Order.user_id == User.id)\
        .order_by(Order.created_at.desc())\
        .limit(5).all()
    
    # Get recent comments with potential XSS
    recent_comments = Comment.query.order_by(Comment.created_at.desc()).limit(5).all()
    
    # Generate XSS test links
    xss_payloads = {
        'reflected': '<script>alert(\'Reflected XSS\')</script>',
        'dom': '<img src=x onerror=alert(\'DOM XSS\')>',
        'stored': '<script>alert(\'Stored XSS\')</script>'
    }
    
    # Generate test URLs
    test_urls = {
        'reflected_xss': url_for('xss_demo', search=xss_payloads['reflected'], _external=True),
        'dom_xss': url_for('xss_test', input=xss_payloads['dom'], _external=True),
        'lfi': url_for('view_file', file='../../etc/passwd', _external=True),
        'idor': url_for('user_profile', user_id=1, _external=True) + ' (coba ganti user_id)',
        'sql_injection': url_for('index', search="' OR '1'='1", _external=True)
    }
    
    return render_template('admin/dashboard.html', 
                         stats=stats, 
                         recent_users=recent_users, 
                         recent_orders=recent_orders,
                         recent_comments=recent_comments,
                         test_urls=test_urls)

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)
