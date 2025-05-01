-- Holistic Prosperity Ministry Payment System Database Setup

-- Create donations database if it doesn't exist
CREATE DATABASE IF NOT EXISTS donations;

-- Use the donations database
USE donations;

-- Donors table
CREATE TABLE IF NOT EXISTS donors (
    donor_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    address VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (email)
);

-- Donation categories table
CREATE TABLE IF NOT EXISTS donation_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default donation categories
INSERT INTO donation_categories (category_name, description) VALUES
('General Fund', 'Support the overall mission and operations of Holistic Prosperity Ministry'),
('CrypStock Academy', 'Support our financial education programs and scholarships'),
('Community Outreach', 'Support our community impact projects and initiatives'),
('Faith & Worship', 'Support our worship services and spiritual growth programs'),
('Building Fund', 'Support the maintenance and expansion of our ministry facilities');

-- Transactions table
CREATE TABLE IF NOT EXISTS transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'USD',
    payment_method ENUM('credit_card', 'mobile_money', 'paypal', 'zelle', 'cashapp', 'bank_transfer') NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    category_id INT,
    is_recurring BOOLEAN DEFAULT FALSE,
    recurring_frequency ENUM('monthly', 'quarterly', 'annual') NULL,
    is_anonymous BOOLEAN DEFAULT FALSE,
    cover_fees BOOLEAN DEFAULT FALSE,
    fee_amount DECIMAL(10, 2) DEFAULT 0.00,
    notes TEXT,
    reference_code VARCHAR(50) UNIQUE,
    in_honor_of VARCHAR(255),
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES donors(donor_id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES donation_categories(category_id) ON DELETE SET NULL
);

-- Stripe payments table
CREATE TABLE IF NOT EXISTS stripe_payments (
    stripe_payment_id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    stripe_charge_id VARCHAR(255),
    stripe_customer_id VARCHAR(255),
    stripe_subscription_id VARCHAR(255),
    card_last_four VARCHAR(4),
    card_brand VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transactions(transaction_id) ON DELETE CASCADE
);

-- Mobile money payments table
CREATE TABLE IF NOT EXISTS mobile_money_payments (
    mobile_money_id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    phone_number VARCHAR(50) NOT NULL,
    provider VARCHAR(100) NOT NULL,
    sender_name VARCHAR(255),
    mobile_reference VARCHAR(255),
    verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    verified_by INT,
    verification_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transactions(transaction_id) ON DELETE CASCADE
);

-- Receipts table
CREATE TABLE IF NOT EXISTS receipts (
    receipt_id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    receipt_number VARCHAR(50) UNIQUE NOT NULL,
    receipt_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tax_deductible BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transactions(transaction_id) ON DELETE CASCADE
);

-- Users table for admin access
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    role ENUM('admin', 'finance', 'viewer') NOT NULL DEFAULT 'viewer',
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (password: AdminHPM2025!)
INSERT INTO users (username, password, first_name, last_name, email, role) VALUES
('admin', '$2y$10$8KgJRLVHNQrRLQzTJxFnDOuKJ.9Wg5txNxD3EHx9WZJvRfMJE4Tla', 'Admin', 'User', 'hello@holisticprosperityministry.org', 'admin');

-- Activity log table
CREATE TABLE IF NOT EXISTS activity_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    entity_type VARCHAR(100),
    entity_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Email templates table
CREATE TABLE IF NOT EXISTS email_templates (
    template_id INT AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default email templates
INSERT INTO email_templates (template_name, subject, body) VALUES
('donation_receipt', 'Thank You for Your Donation to Holistic Prosperity Ministry', '<p>Dear {{donor_name}},</p><p>Thank you for your generous donation of {{amount}} to Holistic Prosperity Ministry. Your contribution will help us continue our mission of empowering individuals and communities through biblical prosperity principles.</p><p>Donation Details:<br>Amount: {{amount}}<br>Date: {{date}}<br>Reference: {{reference_code}}<br>Payment Method: {{payment_method}}</p><p>This donation {{tax_status}} tax-deductible. Please keep this receipt for your tax records.</p><p>With gratitude,<br>Holistic Prosperity Ministry Team</p>'),
('mobile_money_instructions', 'Mobile Money Payment Instructions - Holistic Prosperity Ministry', '<p>Dear {{donor_name}},</p><p>Thank you for choosing to donate to Holistic Prosperity Ministry using Mobile Money. Please follow these instructions to complete your payment:</p><p>1. Send {{amount}} to Mobile Money number: 652444097 (Kort Godlove Fai)<br>2. Use this reference code in your payment description: {{reference_code}}<br>3. After sending the payment, please reply to this email with the transaction details or complete the verification form on our website.</p><p>Your donation will be marked as completed once we verify the payment.</p><p>Thank you for your support!</p><p>Blessings,<br>Holistic Prosperity Ministry Team</p>'),
('payment_verification', 'Your Mobile Money Payment Has Been Verified', '<p>Dear {{donor_name}},</p><p>We are pleased to inform you that your Mobile Money payment of {{amount}} has been verified and recorded in our system.</p><p>Thank you for your generous support of Holistic Prosperity Ministry.</p><p>Donation Details:<br>Amount: {{amount}}<br>Date: {{date}}<br>Reference: {{reference_code}}</p><p>Blessings,<br>Holistic Prosperity Ministry Team</p>');

-- Create indexes for better performance
CREATE INDEX idx_transactions_donor_id ON transactions(donor_id);
CREATE INDEX idx_transactions_category_id ON transactions(category_id);
CREATE INDEX idx_transactions_payment_method ON transactions(payment_method);
CREATE INDEX idx_transactions_payment_status ON transactions(payment_status);
CREATE INDEX idx_transactions_reference_code ON transactions(reference_code);
CREATE INDEX idx_stripe_payments_transaction_id ON stripe_payments(transaction_id);
CREATE INDEX idx_mobile_money_payments_transaction_id ON mobile_money_payments(transaction_id);
CREATE INDEX idx_receipts_transaction_id ON receipts(transaction_id);
