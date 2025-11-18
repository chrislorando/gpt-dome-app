# GPT Dome AI Playground

A free, open-source TALL Stack application showcasing AI-powered mini apps. Rather than just testing models, GPT Dome is a collection of practical AI applications including a chatbot, file validation, CV/resume analysis, and more. Powered by OpenAI.

<img alt="GPT Dome AI Playground" src="https://github.com/user-attachments/assets/2dd5e8cf-9fe1-417e-b391-1f2536ae4331" />

This project serves both as a learning medium to deepen my understanding of modern web development technologies and as a showcase piece for my professional portfolio. The demo is freely accessible online, and the complete source code is available on GitHub for anyone to use, modify, or contribute to.

## Features

- Chat with AI using OpenAI API
- Document verifier
- CV/Resume analysis and feedback
- Expense tracker
- Voice Notes
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
   AWS_ACCESS_KEY_ID=minio
   AWS_SECRET_ACCESS_KEY=xxxxxx
   AWS_DEFAULT_REGION=us-east-1
   AWS_BUCKET=bucket
   AWS_ENDPOINT=https://s3.xxxx.xxx
   AWS_USE_PATH_STYLE_ENDPOINT=true

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

9. Start queue:
   ```
   php artisan queue:listen
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
- Minio

## Notes

The OpenAI file_url API can only access files from public (internet) URLs, not from localhost or a local network. Ensure the files you want OpenAI to process are hosted on public storage like S3, a CDN, or a globally accessible server.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).