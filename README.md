# DursGo Vulnerability Labs

A collection of vulnerable applications to test the DursGo scanner.

---

This repository contains two different vulnerability lab applications, each with its own configuration. Here is how to run both using Docker.

## Lab Descriptions

<p align="center">
  <img src="images/lab-dursgo.png" width="720">
</p>

The following labs are available in this collection:

-   **Open Redirect Lab**
    -   A lab demonstrating a classic Open Redirect vulnerability where the application redirects to any user-supplied URL.

<details>
<summary>Click Video Solved Lab - Open Redirect</summary>

<video src="https://private-user-images.githubusercontent.com/45521655/479785014-a203e41d-8564-4f4c-aa15-1d2d2623a00f.mp4" width="600" controls>
  Your browser does not support the video tag.
</video>
</details>

-   **Blind Vulnerabilities Lab**
    -   Contains both Blind SSRF and Blind Command Injection vulnerabilities, detectable via OAST.

<details>
<summary>Click Video Solved Lab - Blind SSRF</summary>

<video src="https://private-user-images.githubusercontent.com/45521655/479798009-5f9b91bb-35e1-4a91-ab89-fd40ff2487e8.mp4" width="600" controls>
  Your browser does not support the video tag.
</video>
</details>

<details>
<summary>Click Video Solved Lab - Blind Command Injection</summary>

<video src="https://private-user-images.githubusercontent.com/45521655/479790596-634af174-ff48-4dbe-810e-5d0568c0603e.mp4" width="600" controls>
  Your browser does not support the video tag.
</video>
</details>

-   **SSRF (In-Band) Lab**
    -   A classic SSRF where the application fetches and displays content from a user-supplied URL.

<details>
<summary>Click Video Solved Lab - SSRF (In-Band)</summary>

<video src="https://private-user-images.githubusercontent.com/45521655/479798737-4c3c1e3e-82ec-466d-9342-0bd885549116.mp4" width="600" controls>
  Your browser does not support the video tag.
</video>
</details>

-   **Mass Assignment Lab**
    -   This link leads to a protected API endpoint vulnerable to Mass Assignment.

<details>
<summary>Click Video Solved Lab - Mass Assignment</summary>

<video src="https://private-user-images.githubusercontent.com/45521655/479799073-70e66ed2-74d9-4f1a-bbf2-06cd732eb06c.mp4" width="600" controls>
  Your browser does not support the video tag.
</video>
</details>

-   **CORS Misconfiguration Lab**
    -   An API endpoint that improperly reflects the Origin header, allowing data theft from any domain.

<details>
<summary>Click Video Solved Lab - CORS Misconfiguration</summary>

<video src="https://private-user-images.githubusercontent.com/45521655/479799268-829f472e-e211-4cdb-92ea-6b6784230fb0.mp4" width="600" controls>
  Your browser does not support the video tag.
</video>
</details>

-   **Authentication Lab**
    -   A login page to test authenticated scanning capabilities and related vulnerabilities. (Vulnerabilities: CSRF, BOLA, File Upload, Mass Assignment).

<details>
<summary>Click Video Solved Lab - Authentication</summary>

<video src="https://private-user-images.githubusercontent.com/45521655/479801656-be768dc6-133b-4e51-9d50-c3246ff96c6f.mp4" width="600" controls>
  Your browser does not support the video tag.
</video>
</details>

-   **Exposed Files Lab**
    -   A directory to test the detection of sensitive files and folders like `.env` or `.git/`.

<details>
<summary>Click Video Solved Lab - Exposed Files</summary>

<video src="https://private-user-images.githubusercontent.com/45521655/479800336-7ed8ad1a-d2ee-4390-90ec-c2532bfd818a.mp4" width="600" controls>
  Your browser does not support the video tag.
</video>
</details>

-   **IDOR & Stored XSS Lab**
    -   A login authenticated test for Insecure Direct Object References and Stored Cross-Site Scripting.

<details>
<summary>Click Video Solved Lab - IDOR & Stored XSS Lab</summary>

<video src="https://private-user-images.githubusercontent.com/45521655/479801887-c9e4c968-d195-462d-ab58-52cbc54ef4ee.mp4" width="600" controls>
  Your browser does not support the video tag.
</video>
</details>

-   **GraphQL API Lab**
    -   A vulnerable GraphQL endpoint to test for introspection, injection, and other API-specific flaws.

<details>
<summary>Click Video Solved Lab - GraphQL API </summary>

<video src="https://private-user-images.githubusercontent.com/45521655/479800821-a9e345b5-e261-4659-9ba7-8b6a434b77e5.mp4" width="600" controls>
  Your browser does not support the video tag.
</video>
</details>

## 1. PHP Lab Application (`index-vuln`)

This application is a PHP-based vulnerability lab and serves as the main dashboard for all labs.

> **Important:** The main page of this application (`http://localhost:8088`) contains links to all labs, including those running on ports 5000 and 4000. To ensure all links work, you must run **both** applications (`index-vuln` and `index-vuln-2`) simultaneously.

### Requirements
- Docker
- Docker Compose

### How to Run
1.  Open a terminal and navigate to the `index-vuln` directory:
    ```bash
    cd index-vuln
    ```

2.  Run the following command to build and start the Docker container:
    ```bash
    docker compose up --build -d
    ```
    This command will run the application in the background.

3.  Once the container is running, the application will be accessible at:
    [http://localhost:8088](http://localhost:8088)

### How to Stop
To stop the application, run the following command from within the `index-vuln` directory:
```bash
docker compose down
```

---

## 2. Python & Node.js Lab Application (`index-vuln-2`)

This application consists of two services: a Flask-based web application (Python) and a Node.js-based GraphQL API.

### Requirements
- Docker
- Docker Compose

### How to Run
1.  Open a terminal and navigate to the `index-vuln-2` directory:
    ```bash
    cd index-vuln-2
    ```

2.  Run the following command to build and start both services:
    ```bash
    docker compose up --build -d
    ```

3.  Once the containers are running, the services will be accessible at:
    - **Web Application (Flask)**: [http://localhost:5000](http://localhost:5000)
    - **GraphQL API (Node.js)**: [http://localhost:4000](http://localhost:4000)

### Credentials for Web Application (Port 5000)
The database is initialized with the following users:

- **Admin User**
  - **Username:** `admin`
  - **Password:** `admin123`

- **Regular User**
  - **Username:** `user1`
  - **Password:** `password123`

### How to Stop
To stop both services, run the following command from within the `index-vuln-2` directory:
```bash
docker compose down
