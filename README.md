# Holistic Prosperity Ministry Website

A comprehensive website for Holistic Prosperity Ministry, bridging faith and financial empowerment through biblical teachings and practical resources.

## About the Ministry

Holistic Prosperity Ministry, led by Pastor Numfor Prosper, is dedicated to empowering individuals through a holistic approach to prosperity that encompasses spiritual growth, financial wisdom, and community transformation. The ministry believes that true prosperity flows through people to transform communities, not just for personal gain.

## Website Features

- **Responsive Design**: Fully responsive website optimized for all devices
- **Ministry Information**: About us, mission, vision, and core values
- **CrypStock Prosperity Academy**: Educational resources on biblical financial principles
- **Events Calendar**: Upcoming ministry events and activities
- **Donation System**: Multiple payment options including:
  - Credit/Debit Card (via Stripe)
  - Mobile Money (MoMo)
  - Anonymous donation option
- **Contact Form**: Easy communication with the ministry
- **Interactive Elements**: Testimonials, ministry statistics, and impact reports

## Technologies Used

- **Frontend**:
  - HTML5
  - CSS3 (with custom styling)
  - JavaScript (ES6+)
  - Bootstrap 5 (responsive framework)
  - Font Awesome (icons)
  - Google Fonts

- **Backend**:
  - PHP
  - SQLite database
  - Stripe API for payment processing

- **Security Features**:
  - Environment variables in .env file
  - Secure password hashing with bcrypt
  - CSRF protection
  - Input validation and sanitization
  - PCI-compliant payment processing

## Installation and Setup

1. **Clone the repository**:
   ```bash
   git clone https://github.com/yourusername/hpm.git
   cd hpm
   ```

2. **Configure environment variables**:
   - Copy `.env.example` to `.env`
   - Update the values in `.env` with your credentials

3. **Database setup**:
   ```bash
   php database/init_db.php
   ```

4. **Run locally**:
   - Use a local server like XAMPP, MAMP, or PHP's built-in server:
   ```bash
   php -S localhost:8000
   ```

5. **Access the website**:
   - Open your browser and navigate to `http://localhost:8000`

## Website Structure

- **Main Pages**:
  - `index.html`: Homepage with ministry overview
  - `about.html`: About the ministry and leadership
  - `ministries.html`: Various ministry initiatives
  - `events.html`: Upcoming events and calendar
  - `contact.html`: Contact information and form
  - `donate-form.php`: Donation system with multiple payment options

- **Core Functionality**:
  - `js/payment.js`: Handles payment processing logic
  - `includes/`: Core PHP functionality files
  - `database/`: Database setup and management
  - `css/`: Styling files

## Payment System

The website features a comprehensive payment system with:

1. **Credit Card Processing**: Secure Stripe integration
2. **Mobile Money**: Direct mobile money transfers to 652444097 (Kort Godlove Fai)
3. **Anonymous Donations**: Option to donate without providing personal information
4. **WhatsApp Confirmation**: Donation confirmation sent to +14697031453

## Contact Information

- **Ministry Location**: Heartland, Texas, United States
- **Phone**: +1 (469) 703-1453
- **Email**: hello@holisticprosperityministry.org

## Contributing

1. Fork the repository
2. Create your feature branch: `git checkout -b feature/amazing-feature`
3. Commit your changes: `git commit -m 'Add some amazing feature'`
4. Push to the branch: `git push origin feature/amazing-feature`
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.
