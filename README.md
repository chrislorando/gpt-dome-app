# Demolite AI Playground

A TALL Stack application that functions as a ChatGPT-like chat app, integrating with OpenAI API for AI conversations. 

This project serves both as a learning medium to deepen my understanding of modern web development technologies and as a showcase piece for my professional portfolio.

## Features

- Chat with AI using OpenAI API
- Conversation history management
- Search conversations
- Real-time chat updates with Livewire stream
- Responsive UI with TailwindCSS and Flux UI

## Installation

1. Clone the repository:
   ```
   git clone <repository-url>
   cd ai-playground-app
   ```

2. Install PHP dependencies:
   ```
   composer install
   ```

3. Copy the environment file and configure it:
   ```
   cp .env.example .env
   ```
   Configure the following in `.env`:
   ```
   OPENAI_API_KEY=your_openai_api_key
   OPENAI_ORGANIZATION=your_openai_org
   ```
   (Database is configured to use SQLite by default. If you prefer MySQL, update `DB_CONNECTION` and related settings.)

4. Generate application key:
   ```
   php artisan key:generate
   ```

5. Run database migrations:
   ```
   php artisan migrate
   ```

6. Install Node.js dependencies:
   ```
   npm install
   ```

7. Build assets:
   ```
   npm run build
   ```

8. Start the development server:
   ```
   php artisan serve
   ```


## Usage

- Access the application at `http://localhost:8000` (or your configured URL).
- Register/Login to start chatting.
- Create new conversations, search existing ones, and interact with the AI.

## Requirements

- PHP 8.3+
- Composer
- Node.js & npm
- MySQL or compatible database (SQLite by default)
- OpenAI API key

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).