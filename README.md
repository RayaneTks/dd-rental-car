
Built by https://www.blackbox.ai

---

```markdown
# DD RENTAL CAR

## Project Overview
DD RENTAL CAR is a web application that allows users to rent premium vehicles in Marseille and the surrounding PACA region. The application provides a simple and efficient platform to browse available vehicles, make reservations, and deliver vehicles right to the customer's doorstep.

## Installation
To set up the project locally, follow the steps below:

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/dd-rental-car.git
   cd dd-rental-car
   ```

2. **Set up the environment**
   Ensure you have the necessary server environment (e.g., Apache, Nginx) with PHP support. If you're using SQLite, no additional database server setup is required.

3. **Initialize the database**
   Run the `init_db.php` script to initialize the database and create the required tables:
   ```bash
   php init_db.php
   ```

4. **Configure your web server**
   Point your web server to the project root directory to serve the application.

5. **Access the application**
   Open your web browser and navigate to `http://localhost/dd-rental-car/index.php` (or the URL relevant to your setup).

## Usage
- **Browse Vehicles**: Visit the vehicle catalog to see the premium cars available for rental.
- **Make a Reservation**: Select a vehicle from the catalog to proceed to the reservation form.
- **Contact Support**: If you have questions or special requests, contact support via the provided WhatsApp link.

## Features
- **Premium Vehicle Selection**: Users can browse a variety of luxury cars such as BMW, Mercedes, and Audi.
- **Easy Reservation Process**: Customers can easily reserve vehicles through an online form.
- **Free Delivery in PACA Region**: The service offers free delivery to locations within the PACA region.
- **User Testimonials**: Includes testimonials section to enhance credibility and service quality.
- **FAQ Section**: Provides answers to common customer inquiries regarding the rental process, confirmation times, and more.

## Dependencies
The project uses the following libraries and frameworks:
- **Tailwind CSS** for styling.
- **Alpine.js** for interactivity.
- **Flatpickr** for date selection in the reservation form.
- **PHP PDO** for database operations.

## Project Structure
```
.
├── index.php              # Home page of the application
├── catalog.php            # Page for browsing available vehicles
├── reservation.php        # Reservation form for selected vehicle
├── error.php              # Custom error page
├── init_db.php           # Script for initializing the database
├── includes/              # Directory for included PHP files
│   ├── config.php        # Configuration file for database connection
│   ├── db.php            # Database connection and query logic
│   └── functions.php     # Helper functions used throughout the application
├── public/                # Public assets (optional)
│   ├── css/              # Custom stylesheets
│   └── images/           # Images used in the application
└── README.md              # This README file
```

## License
This project is open-source and available for personal and commercial use. Please consider contributing if you find it useful!
```

Replace the GitHub URL in the Installation section with your actual repository URL.