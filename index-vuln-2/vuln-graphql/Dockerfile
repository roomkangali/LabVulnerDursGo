# Use an official Node.js runtime as a parent image
FROM node:16

# Set the working directory in the container
WORKDIR /usr/src/app

# Copy package.json and package-lock.json
COPY package*.json ./

# Install app dependencies
RUN npm install

# Bundle app source
COPY . .

# Make port 4000 available to the world outside this container
EXPOSE 4000

# Run the command to start the app
CMD [ "node", "server.js" ]
