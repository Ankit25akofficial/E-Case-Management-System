# Multi-stage build
FROM node:20-alpine AS builder

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build

# Production server stage
FROM nginx:alpine

COPY --from=builder /app/dist /usr/share/nginx/html

# Custom Nginx configuration to support SPA routing
COPY nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
