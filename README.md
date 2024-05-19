# Stock and Sales Management System

This project is a web-based application built with Symfony that allows you to manage stock and sales for your business. It provides features for tracking inventory, managing products, and generating sales reports.

## Features

- Product Management: Add, edit, and delete products with details such as name, description, price, and quantity.
- Inventory Tracking: Keep track of stock levels and receive notifications when stock runs low.
- Sales Management: Record sales transactions, generate invoices, and track customer information.
- Reporting: Generate various reports, including sales reports, inventory reports, and customer insights.
- User Authentication: Secure access to the system with Google OAuth authentication.

## Prerequisites

Before running the application, ensure that you have the following installed:

- PHP 8.0 or higher
- Composer
- MySQL or any other supported database
- Web server (e.g., Apache, Nginx)

## Installation

1. Clone the repository:

   ```
   git clone https://github.com/thierry1804/duoADV.git
   ```

2. Navigate to the project directory:

   ```
   cd duoADV
   ```

3. Install the dependencies using Composer:

   ```
   composer install
   ```

4. Create a copy of the `.env` file and configure the database connection:

   ```
   cp .env .env.local
   ```

   Open the `.env.local` file and update the `DATABASE_URL` parameter with your database credentials.

5. Set up Google OAuth:

   - Create a new project in the Google Developers Console (https://console.developers.google.com/).
   - Enable the Google+ API for your project.
   - Create OAuth 2.0 client credentials and obtain the client ID and client secret.
   - Update the `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET` parameters in the `.env.local` file with your Google OAuth credentials.

6. Create the database schema:

   ```
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

7. Start the development server:

   ```
   symfony server:start
   ```

   The application should now be accessible at `http://localhost:8000`.

## Usage

1. Access the application in your web browser.

2. Click on the "Sign in with Google" button to authenticate using your Google account.

3. Once authenticated, navigate through the different sections of the application, such as Products, Inventory, Sales, and Reporting.

4. Add, edit, or delete products as needed.

5. Record sales transactions and generate invoices.

6. Monitor stock levels and receive notifications when stock runs low.

7. Generate reports to gain insights into sales, inventory, and customer trends.

## Contributing

Contributions are welcome! If you find any bugs or have suggestions for improvements, please open an issue or submit a pull request.

## License

This project is licensed under the [MIT License](LICENSE).

## Contact

For any inquiries or feedback, please contact [thierry1804@gmail.com](mailto:thierry1804@gmail.com).
