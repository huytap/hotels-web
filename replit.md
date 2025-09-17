# Overview

This is a Vietnamese web development services landing page built as a full-stack application. The application serves as a professional marketing website for a web development consultant, featuring service offerings, client testimonials, portfolio showcase, and lead generation forms. The site is designed to attract potential clients and convert visitors into leads through trial requests and contact inquiries.

# User Preferences

Preferred communication style: Simple, everyday language.

# System Architecture

## Frontend Architecture

**React + TypeScript SPA**: The client-side application is built using React 18 with TypeScript, providing a modern, type-safe development experience. The application uses Vite as the build tool for fast development and optimized production builds.

**Component-Based Design**: The UI is structured around reusable components organized into sections (Header, Hero, About, Services, Clients, Trial, Contact, Footer). Each component is self-contained with its own props interface and styling.

**Styling Framework**: Uses Tailwind CSS with a custom configuration that extends the base theme with brand-specific colors and design tokens. The design system follows professional B2B aesthetics inspired by companies like Vercel and Netlify.

**UI Component Library**: Implements shadcn/ui components built on top of Radix UI primitives, providing accessible and consistent interface elements like buttons, cards, forms, and dialogs.

**Routing**: Uses Wouter for client-side routing, with a simple route structure that currently handles the main landing page and 404 errors.

## Backend Architecture

**Express.js REST API**: The server is built with Express.js and TypeScript, providing RESTful endpoints for form submissions and data persistence.

**Database Layer**: Uses Drizzle ORM with PostgreSQL for data management. The database schema includes tables for users, contact submissions, and trial requests with proper validation and constraints.

**Storage Abstraction**: Implements an IStorage interface that allows switching between different storage backends (currently includes an in-memory implementation for development and database implementation for production).

**Form Processing**: Provides dedicated API endpoints (`/api/contact` and `/api/trial`) that validate incoming form data using Zod schemas and store submissions in the database.

## Data Management

**Type-Safe Schemas**: Uses Drizzle ORM with Zod for runtime validation, ensuring data integrity between frontend forms and backend storage.

**Query Management**: Implements React Query for client-side state management, caching, and API interactions.

**Session Handling**: Includes session management capabilities using connect-pg-simple for PostgreSQL session storage.

## Development Workflow

**Build System**: Vite handles frontend bundling with hot module replacement, while esbuild bundles the Node.js server for production deployment.

**Type Safety**: Comprehensive TypeScript configuration with strict type checking across the entire stack.

**Path Aliases**: Configured path mapping for clean imports (`@/` for client components, `@shared/` for shared utilities).

# External Dependencies

## Database
- **Neon PostgreSQL**: Cloud PostgreSQL database service for production data storage
- **Drizzle Kit**: Database migration and schema management tool

## UI Components
- **Radix UI**: Headless UI primitives for accessibility and interaction patterns
- **Tailwind CSS**: Utility-first CSS framework for styling
- **Lucide React**: Icon library providing consistent SVG icons

## Form Handling
- **React Hook Form**: Form state management and validation
- **Zod**: Runtime type validation for form data

## Development Tools
- **Vite**: Frontend build tool and development server
- **React Query**: Server state management and caching
- **TypeScript**: Static type checking
- **ESBuild**: Server bundling for production

## Fonts & Assets
- **Google Fonts**: Inter for primary typography, JetBrains Mono for code elements
- **Generated Images**: AI-generated placeholder images for hero sections and portfolio examples

## Replit Integration
- **Runtime Error Modal**: Development error handling
- **Cartographer**: Code mapping for debugging in Replit environment