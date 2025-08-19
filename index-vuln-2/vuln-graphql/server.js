const express = require('express');
const { ApolloServer, gql } = require('apollo-server-express');
const sqlite3 = require('sqlite3').verbose();
const bodyParser = require('body-parser');

// Initialize SQLite database
const db = new sqlite3.Database(':memory:');

// Create table and dummy data
db.serialize(() => {
  db.run("CREATE TABLE users (id INTEGER PRIMARY KEY, username TEXT, password TEXT, isAdmin BOOLEAN, email TEXT, api_key TEXT)");
  db.run("INSERT INTO users (username, password, isAdmin, email, api_key) VALUES ('admin', 'admin123', true, 'admin@example.com', 'admin-api-key-123')");
  db.run("INSERT INTO users (username, password, isAdmin, email, api_key) VALUES ('user1', 'password123', false, 'user1@example.com', 'user1-api-key-456')");
  
  // Create table for sensitive data
  db.run("CREATE TABLE sensitive_data (id INTEGER PRIMARY KEY, name TEXT, ssn TEXT, credit_card TEXT, address TEXT)");
  db.run("INSERT INTO sensitive_data (name, ssn, credit_card, address) VALUES ('John Doe', '123-45-6789', '4111111111111111', '123 Main St')");
  
  // Table to store login logs
  db.run(`
    CREATE TABLE IF NOT EXISTS login_attempts (
      id INTEGER PRIMARY KEY, 
      username TEXT, 
      timestamp DATETIME DEFAULT CURRENT_TIMESTAMP, 
      ip_address TEXT,
      user_agent TEXT
    )
  `);
});

// Define GraphQL schema
const typeDefs = gql`
  type User {
    id: ID!
    username: String!
    password: String!
    email: String
    apiKey: String
    isAdmin: Boolean!
  }

  type SensitiveData {
    id: ID!
    name: String!
    ssn: String!
    creditCard: String!
    address: String
  }

  type LoginAttempt {
    id: ID!
    username: String
    timestamp: String
    ipAddress: String
  }

  type HeavyQueryResult {
    message: String
    total: Int
  }

  type Query {
    # Query vulnerable to SQL Injection
    user(id: String): User
    users: [User!]!
    searchUsers(query: String!): [User!]!
    
    # Query that returns sensitive data
    sensitiveData: [SensitiveData!]!
    
    # Very heavy query for rate limiting demo
    heavyQuery: HeavyQueryResult
    
    # View login logs (without authorization)
    loginLogs: [LoginAttempt!]!
  }

  type Mutation {
    # Mutation vulnerable to NoSQL Injection
    login(username: String!, password: String!): String
    
    # Mutation that has no input validation
    updateProfile(id: ID!, data: String!): String
  }
`;

// Define resolvers
const resolvers = {
  Query: {
    // Vulnerable to SQL Injection - Version 1 (classic)
    user: (_, { id }) => {
      return new Promise((resolve, reject) => {
        // SQL Injection vulnerable code
        db.get(`SELECT * FROM users WHERE id = ${id}`, [], (err, row) => {
          if (err) {
            console.error('Error in query:', err);
            reject(err);
          } else {
            resolve(row);
          }
        });
      });
    },
    
    // Vulnerable to SQL Injection - Version 2 (with search)
    searchUsers: (_, { query }) => {
      return new Promise((resolve, reject) => {
        // SQL Injection vulnerable code
        db.all(`SELECT * FROM users WHERE username LIKE '%${query}%' OR email LIKE '%${query}%'`, [], (err, rows) => {
          if (err) {
            console.error('Error in search query:', err);
            reject(err);
          } else {
            resolve(rows);
          }
        });
      });
    },
    
    // Very heavy endpoint - for rate limiting demo
    heavyQuery: () => {
      return new Promise((resolve) => {
        // Heavy query to slow down the server
        db.all(`WITH RECURSIVE counter(x) AS (
          SELECT 1
          UNION ALL
          SELECT x+1 FROM counter
          LIMIT 1000000
        ) SELECT SUM(x) as total FROM counter`, [], (err, result) => {
          if (err) {
            console.error('Error in heavy query:', err);
            resolve({ message: "Error in heavy query" });
          } else {
            const total = result && result[0] ? result[0].total : 0;
            resolve({ 
              message: "Heavy query completed",
              total: total
            });
          }
        });
      });
    },
    
    // Returns all users (including password in plaintext)
    users: () => {
      return new Promise((resolve, reject) => {
        db.all("SELECT * FROM users", [], (err, rows) => {
          if (err) {
            reject(err);
          } else {
            resolve(rows);
          }
        });
      });
    },
    
    // Returns sensitive data without authentication
    sensitiveData: () => {
      return new Promise((resolve, reject) => {
        db.all("SELECT * FROM sensitive_data", [], (err, rows) => {
          if (err) {
            reject(err);
          } else {
            resolve(rows.map(row => ({
              ...row,
              creditCard: row.credit_card // Field alias for schema consistency
            })));
          }
        });
      });
    },
    
    // Returns login logs without authorization
    loginLogs: () => {
      return new Promise((resolve, reject) => {
        db.all("SELECT * FROM login_attempts ORDER BY timestamp DESC LIMIT 100", [], (err, rows) => {
          if (err) {
            console.error('Error fetching login logs:', err);
            reject(err);
          } else {
            // Format the result according to the LoginAttempt type
            const logs = rows.map(row => ({
              id: row.id,
              username: row.username,
              timestamp: row.timestamp,
              ipAddress: row.ip_address
            }));
            resolve(logs);
          }
        });
      });
    }
  },
  
  Mutation: {
    // Login with NoSQL Injection vulnerability
    login: (_, { username, password }) => {
      return new Promise((resolve) => {
        // NoSQL Injection vulnerable code
        const query = `SELECT * FROM users WHERE username = '${username}' AND password = '${password}'`;
        db.get(query, [], (err, row) => {
          if (err || !row) {
            resolve('Login failed');
          } else {
            resolve(`Welcome ${row.username}! ${row.isAdmin ? 'You are admin!' : ''}`);
          }
        });
      });
    },
    
    // Update profile without input validation
    updateProfile: (_, { id, data }) => {
      return new Promise((resolve) => {
        // No input validation
        resolve(`Profile ${id} updated with data: ${data}`);
      });
    }
  }
};

async function startServer() {
  const app = express();
  
  // Middleware for parsing JSON
  app.use(bodyParser.json({ limit: '10mb' }));
  
  // Middleware for logging all requests (without rate limiting)
  app.use((req, res, next) => {
    console.log(`[${new Date().toISOString()}] ${req.method} ${req.path}`);
    next();
  });
  
  // Apollo Server configuration
  const server = new ApolloServer({
    typeDefs,
    resolvers,
    // Enable introspection (default: true)
    introspection: true,
    // Disable playground in production (but enabled for demo)
    playground: true,
    // Disable cache for development
    cacheControl: false,
    // Debug mode
    debug: true,
    // More detailed error format (not safe for production)
    formatError: (err) => {
      console.error('GraphQL Error:', err);
      return {
        message: err.message,
        locations: err.locations,
        path: err.path,
        // In production, don't show stack trace
        ...(process.env.NODE_ENV !== 'production' && { stack: err.extensions?.exception?.stacktrace })
      };
    }
  });

  await server.start();
  
  // Apply Apollo middleware
  server.applyMiddleware({ 
    app,
    path: '/graphql',
    bodyParserConfig: { limit: '10mb' }
  });

  // Route for health check
  app.get('/health', (req, res) => {
    res.json({ 
      status: 'ok', 
      timestamp: new Date().toISOString(),
      uptime: process.uptime()
    });
  });
  
  // Route to view login logs (without authentication)
  app.get('/api/logs', (req, res) => {
    db.all('SELECT * FROM login_attempts ORDER BY timestamp DESC LIMIT 100', [], (err, logs) => {
      if (err) {
        console.error('Error fetching logs:', err);
        return res.status(500).json({ error: 'Internal server error' });
      }
      res.json(logs);
    });
  });
  
  // Very vulnerable SQL Injection endpoint with clear feedback
  app.get('/api/users/search', (req, res) => {
    const { q, sort = 'id' } = req.query;
    
    if (!q) {
      // Provide an example of usage that triggers SQL Injection
      const exampleUrl = '/api/users/search?q=admin\' UNION SELECT 1,2,3,4,5,6--';
      return res.status(400).json({ 
        error: 'Query parameter q is required', 
        example: `${req.protocol}://${req.get('host')}${exampleUrl}`,
        vulnerable: true,
        description: 'This endpoint is vulnerable to SQL Injection. Try the example URL.'
      });
    }
    
    console.log(`[SQLi] Executing search with query: ${q}`);
    
    // Vulnerable to SQL Injection with clear errors
    const query = `SELECT * FROM users WHERE username LIKE '%${q}%' OR email LIKE '%${q}%'`;
    console.log(`[SQLi] Executing query: ${query}`);
    
    db.all(query, [], (err, rows) => {
      if (err) {
        console.error('Error in search:', err);
        // Return the full SQL error to facilitate detection
        return res.status(500).json({ 
          error: 'Database error',
          details: err.message,
          query: query,
          vulnerable: true
        });
      }
      
      // Add vulnerability flag in the response
      const response = {
        data: rows,
        _meta: {
          query: query,
          vulnerable: true,
          description: 'This endpoint is vulnerable to SQL Injection',
          example: `${req.protocol}://${req.get('host')}/api/users/search?q=admin' UNION SELECT 1,2,3,4,5,6--`
        }
      };
      
      res.json(response);
    });
  });
  
  // Special endpoint for SQL Injection testing
  app.get('/api/vulnerable/search', (req, res) => {
    const { id } = req.query;
    
    if (!id) {
      return res.status(400).json({
        error: 'ID parameter is required',
        example: `${req.protocol}://${req.get('host')}/api/vulnerable/search?id=1 OR 1=1`,
        vulnerable: true
      });
    }
    
    const query = `SELECT * FROM users WHERE id = ${id}`;
    console.log(`[SQLi] Executing vulnerable query: ${query}`);
    
    db.all(query, [], (err, rows) => {
      if (err) {
        return res.status(500).json({
          error: 'Database error',
          details: err.message,
          query: query,
          vulnerable: true
        });
      }
      
      res.json({
        data: rows,
        _meta: {
          query: query,
          vulnerable: true,
          description: 'This is a deliberately vulnerable endpoint for security testing'
        }
      });
    });
  });
  
  // Login endpoint vulnerable to SQL Injection and without rate limiting
  app.post('/api/login', (req, res) => {
    const { username, password } = req.body;
    const ip = req.ip || req.connection.remoteAddress;
    const userAgent = req.get('User-Agent') || 'unknown';
    
    // Log all request details for debugging purposes
    console.log(`[Login Attempt] Username: ${username} from IP: ${ip}, User-Agent: ${userAgent}`);
    
    // Log all login attempts (without rate limiting)
    db.run(
      'INSERT INTO login_attempts (username, ip_address, user_agent) VALUES (?, ?, ?)',
      [username, ip, userAgent],
      (err) => {
        if (err) console.error('Failed to log login attempt:', err);
      }
    );
    
    // Vulnerable to SQL Injection with clear errors
    const query = `SELECT * FROM users WHERE username = '${username}' AND password = '${password}'`;
    console.log(`[SQLi] Login query: ${query}`);
    
    db.get(query, [], (err, user) => {
      if (err) {
        console.error('Login error:', err);
        return res.status(500).json({ 
          success: false, 
          message: 'Internal server error',
          error: process.env.NODE_ENV === 'development' ? err.message : undefined,
          query: query,
          vulnerable: true
        });
      }
      
      if (user) {
        console.log(`[Login Success] User: ${user.username} (ID: ${user.id})`);
        res.json({ 
          success: true, 
          user: { 
            id: user.id, 
            username: user.username, 
            email: user.email,
            isAdmin: user.isAdmin 
          },
          _meta: {
            query: query,
            vulnerable: true,
            rate_limited: false,
            description: 'This login endpoint is vulnerable to SQL Injection and has no rate limiting.'
          }
        });
      } else {
        console.log(`[Login Failed] Invalid credentials for username: ${username}`);
        res.status(401).json({ 
          success: false, 
          message: 'Invalid credentials',
          _meta: {
            query: query,
            vulnerable: true,
            rate_limited: false,
            description: 'This login endpoint is vulnerable to SQL Injection and has no rate limiting.'
          }
        });
      }
    });
  });
  
  // Special endpoint for rate limiting testing
  app.get('/api/vulnerable/rate-limit', (req, res) => {
    // No rate limiting at all
    const requestCount = parseInt(req.query.count || 1);
    
    // Simulate a light workload
    let result = 0;
    for (let i = 0; i < 1000 * requestCount; i++) {
      result += Math.random();
    }
    
    res.json({
      success: true,
      request: requestCount,
      result: result,
      _meta: {
        rate_limited: false,
        vulnerable: true,
        description: 'This endpoint has no rate limiting and is vulnerable to DoS attacks.',
        recommendation: 'Implement rate limiting to prevent abuse.'
      }
    });
  });
  
  // Endpoint for NoSQL Injection testing
  app.get('/api/vulnerable/nosql', (req, res) => {
    const { username, isAdmin } = req.query;
    
    // Simulate NoSQL Injection vulnerability
    let query = 'SELECT * FROM users WHERE 1=1';
    
    if (username) {
      query += ` AND username = '${username.replace(/'/g, "''")}'`;
    }
    
    if (isAdmin !== undefined) {
      // Intentionally vulnerable to NoSQL Injection
      query += ` AND isAdmin = ${isAdmin === 'true' ? 1 : 0}`;
    }
    
    console.log(`[NoSQLi] Executing query: ${query}`);
    
    db.all(query, [], (err, users) => {
      if (err) {
        return res.status(500).json({
          error: 'Database error',
          details: err.message,
          query: query,
          vulnerable: true
        });
      }
      
      res.json({
        data: users,
        _meta: {
          query: query,
          vulnerable: true,
          description: 'This endpoint is vulnerable to NoSQL Injection',
          example: `${req.protocol}://${req.get('host')}/api/vulnerable/nosql?username=admin&isAdmin=true`
        }
      });
    });
  });
  
  // Endpoint for NoSQL Injection demo (if using a NoSQL database)
  app.get('/api/v1/users', (req, res) => {
    const { username, isAdmin } = req.query;
    
    // Example of NoSQL Injection vulnerability if using MongoDB
    // Here we only simulate with a regular SQL query
    let query = 'SELECT * FROM users WHERE 1=1';
    const params = [];
    
    if (username) {
      query += ` AND username = '${username}'`;
    }
    
    if (isAdmin !== undefined) {
      query += ` AND isAdmin = ${isAdmin === 'true' ? 1 : 0}`;
    }
    
    console.log(`[NoSQLi] Executing query: ${query}`);
    
    db.all(query, params, (err, users) => {
      if (err) {
        console.error('Error in NoSQLi endpoint:', err);
        return res.status(500).json({ error: 'Internal server error' });
      }
      res.json(users);
    });
  });
  
  // Endpoint for SSRF (Server-Side Request Forgery) demo
  app.get('/api/fetch', (req, res) => {
    const { url } = req.query;
    
    if (!url) {
      return res.status(400).json({ error: 'URL parameter is required' });
    }
    
    console.log(`[SSRF] Fetching URL: ${url}`);
    
    // Vulnerable to SSRF because it does not validate the URL
    const https = require('https');
    const parsedUrl = new URL(url);
    
    const options = {
      hostname: parsedUrl.hostname,
      port: parsedUrl.port || 443,
      path: parsedUrl.pathname + parsedUrl.search,
      method: 'GET',
      // Do not follow redirects to prevent chained SSRF attacks
      maxRedirects: 0,
      // Timeout to prevent DoS attacks
      timeout: 5000
    };
    
    const request = https.request(options, (response) => {
      let data = '';
      
      response.on('data', (chunk) => {
        data += chunk;
      });
      
      response.on('end', () => {
        try {
          res.json(JSON.parse(data));
        } catch (e) {
          res.send(data);
        }
      });
    });
    
    request.on('error', (error) => {
      console.error(`[SSRF Error] ${error.message}`);
      res.status(500).json({ 
        error: 'Failed to fetch URL',
        details: error.message 
      });
    });
    
    // Set timeout to prevent hanging requests
    request.setTimeout(5000, () => {
      request.destroy();
      console.error(`[SSRF Timeout] Request to ${url} timed out`);
      res.status(504).json({ 
        error: 'Request timed out',
        details: 'The request took too long to complete'
      });
    });
    
    request.end();
  });

  // Run the server
  const PORT = process.env.PORT || 4000;
  app.listen(PORT, () => {
    console.log(`🚀 Vulnerable GraphQL server running at http://localhost:${PORT}${server.graphqlPath}`);
    console.log(`📊 GraphQL Playground available at http://localhost:${PORT}${server.graphqlPath}`);
    console.log('\n🔍 Try the vulnerable endpoints:');
    console.log('   - SQL Injection:');
    console.log('     GET  /api/users/search?q=admin\' OR \'1\'=\'1');
    console.log('     POST /api/login with body { "username": "admin\' --", "password": "anything" }');
    console.log('     GraphQL: { user(id: "1 OR 1=1") { id, username, email, apiKey } }');
    console.log('   - NoSQL Injection:');
    console.log('     GET /api/v1/users?username=admin\' OR \'1\'=\'1&isAdmin=true');
    console.log('   - SSRF:');
    console.log('     GET /api/fetch?url=https://example.com');
    console.log('   - Missing Rate Limiting:');
    console.log('     Make many requests to the /api/login endpoint with random usernames and passwords');
    console.log('   - Sensitive Data Exposure:');
    console.log('     GET /api/logs (displays login logs)');
    console.log('     GraphQL: { sensitiveData { name, ssn, creditCard, address } }');
    console.log('\n⚠️  WARNING: This server is intentionally vulnerable for security testing purposes!');
    console.log('   DO NOT use in a production environment!');
  });
}

startServer().catch(error => {
  console.error('Failed to start server:', error);
  process.exit(1);
});
