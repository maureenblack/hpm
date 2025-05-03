-- SQLite Schema for Holistic Prosperity Ministry Payment System

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    user_id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    role TEXT NOT NULL DEFAULT 'viewer',
    is_active INTEGER NOT NULL DEFAULT 1,
    last_login TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Donations Table
CREATE TABLE IF NOT EXISTS donations (
    donation_id INTEGER PRIMARY KEY AUTOINCREMENT,
    donor_name TEXT,
    donor_email TEXT,
    amount DECIMAL(10,2) NOT NULL,
    payment_method TEXT NOT NULL,
    payment_status TEXT NOT NULL DEFAULT 'pending',
    transaction_id TEXT,
    is_recurring INTEGER NOT NULL DEFAULT 0,
    donation_type TEXT NOT NULL DEFAULT 'ministry',
    donation_purpose TEXT,
    notes TEXT,
    ip_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- CrypStock Academy Payments
CREATE TABLE IF NOT EXISTS academy_payments (
    payment_id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_name TEXT NOT NULL,
    student_email TEXT NOT NULL,
    course_id INTEGER,
    course_name TEXT,
    amount DECIMAL(10,2) NOT NULL,
    payment_method TEXT NOT NULL,
    payment_status TEXT NOT NULL DEFAULT 'pending',
    transaction_id TEXT,
    is_recurring INTEGER NOT NULL DEFAULT 0,
    subscription_id TEXT,
    notes TEXT,
    ip_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Courses Table
CREATE TABLE IF NOT EXISTS courses (
    course_id INTEGER PRIMARY KEY AUTOINCREMENT,
    course_name TEXT NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    subscription_price DECIMAL(10,2),
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Mobile Money Verification
CREATE TABLE IF NOT EXISTS momo_verification (
    verification_id INTEGER PRIMARY KEY AUTOINCREMENT,
    payment_id INTEGER,
    payment_type TEXT NOT NULL, -- 'donation' or 'academy'
    reference_number TEXT NOT NULL,
    phone_number TEXT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    is_verified INTEGER NOT NULL DEFAULT 0,
    verified_by INTEGER,
    verified_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (verified_by) REFERENCES users(user_id)
);

-- Email Templates
CREATE TABLE IF NOT EXISTS email_templates (
    template_id INTEGER PRIMARY KEY AUTOINCREMENT,
    template_name TEXT NOT NULL UNIQUE,
    subject TEXT NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Activity Log
CREATE TABLE IF NOT EXISTS activity_log (
    log_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action TEXT NOT NULL,
    details TEXT,
    ip_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Insert default admin user (password: admin123)
INSERT OR IGNORE INTO users (username, password, first_name, last_name, email, role)
VALUES ('admin', '$2y$12$6NZ.xnX9BzBQdKbJH7dVEOzE5uQBDz5NurS1XcBVraBjYKB2.Qhoe', 'System', 'Administrator', 'admin@holisticprosperityministry.org', 'admin');

-- Insert default email templates
INSERT OR IGNORE INTO email_templates (template_name, subject, body)
VALUES 
('donation_receipt', 'Thank You for Your Donation to Holistic Prosperity Ministry', '<p>Dear {{donor_name}},</p><p>Thank you for your generous donation of {{amount}} to Holistic Prosperity Ministry. Your contribution helps us continue our mission.</p><p>Donation Details:<br>Amount: {{amount}}<br>Date: {{date}}<br>Transaction ID: {{transaction_id}}</p><p>Blessings,<br>Holistic Prosperity Ministry Team</p>'),
('payment_receipt', 'CrypStock Academy Payment Confirmation', '<p>Dear {{student_name}},</p><p>Thank you for your payment of {{amount}} for the {{course_name}} course at CrypStock Academy.</p><p>Payment Details:<br>Amount: {{amount}}<br>Date: {{date}}<br>Transaction ID: {{transaction_id}}</p><p>Blessings,<br>CrypStock Academy Team</p>');

-- Insert sample courses
INSERT OR IGNORE INTO courses (course_name, description, price, subscription_price)
VALUES 
('Cryptocurrency Basics', 'Introduction to cryptocurrency concepts and blockchain technology', 99.99, 9.99),
('Advanced Trading Strategies', 'Learn advanced trading techniques for cryptocurrency markets', 199.99, 19.99),
('Biblical Financial Wisdom', 'Understanding financial principles from a biblical perspective', 79.99, 7.99);
