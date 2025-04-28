# 🏥 GitHub Copilot Prompt – Pharmacity Store API Backend

## Project Overview
Create a secure, optimized Laravel API backend for Pharmacity Store, an e-commerce platform specializing in pharmaceutical products. The backend will handle data processing, medication queries, customer support, and order management using RESTful principles and JWT authentication.

## Core Features to Implement
- CRUD APIs for products, categories, shopping cart, and orders
- User authentication and authorization with JWT
- MongoDB integration for product data, orders, and AI embeddings
- AI consultation system using LLaMA 3.3 + RAG for customer support
- Order history and tracking system
- Real-time pharmacist consultation
- Performance optimization with caching and queue jobs

## Technical Requirements
- Laravel 11 for API backend development
- MongoDB Atlas for storing products, orders, and embedding data
- JWT for secure user authentication
- Redis for queue jobs (email sending, AI chatbot processing)
- Swagger/Postman for API documentation
- PHPUnit for comprehensive API testing

## Development Tasks
1. **API Structure**:
  - Follow RESTful principles for all endpoints
  - Use Laravel Routes Attributes for route configuration
  - Implement Controller.php base response patterns (`$this->json()` or `$this->fail()`)

2. **Database Integration**:
  - Configure MongoDB connections for products and orders
  - Design optimal schema for medication data (ingredients, usage, dosage, contraindications)
  - Implement efficient query patterns for fast data retrieval

3. **Authentication System**:
  - Set up JWT token generation and validation
  - Create role-based access control (customer, pharmacist, admin)
  - Implement secure password handling and account management

4. **AI Chatbot Integration**:
  - Build API endpoints for LLaMA 3.3 + RAG integration
  - Set up MongoDB for storing and querying embedding data
  - Implement conversational flows for medication inquiries

5. **Performance Optimization**:
  - Implement Redis caching for frequently accessed data
  - Set up queue jobs for background processing
  - Add rate limiting and API throttling for security

6. **Testing and Documentation**:
  - Write PHPUnit tests for all API endpoints
  - Generate comprehensive API documentation
  - Create environment setup guides for development

## Expected Results
- Fast, secure, and scalable API backend
- Seamless integration with frontend systems
- Optimized MongoDB queries for quick response times
- Secure authentication with JWT
- Robust AI chatbot functionality with LLaMA 3.3 + RAG
- Well-documented and thoroughly tested codebase

## Git Commit Conventions
- Use semantic commit messages: `type(scope): message`
- Types: feat, fix, docs, style, refactor, test, chore
- Example: `feat(cart): add discount code validation`
- Keep commits atomic and focused on single changes
- Branch naming: `feature/feature-name`, `bugfix/issue-description`
- Include issue numbers when applicable: `fix(auth): resolve login timeout (#123)`