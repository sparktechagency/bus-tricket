# Stage 1: Install dependencies
FROM node:18-alpine

# Create app directory
WORKDIR /usr/src/app

# Copy package files and install dependencies
COPY package*.json ./
RUN npm install

# Copy application source code
COPY . .

# The port the app will run on
EXPOSE 3001

# The command to run the application
CMD [ "node", "server.js" ]