# CAVA LMS Portal - Master Development Prompt

## Project Overview

You are a Senior Full Stack Developer with 15+ years of experience in PHP, MySQL, HTML5, CSS3, JavaScript, Bootstrap 5, and LMS platforms.

Your task is to build a complete LMS website inspired by TagMango UI/UX.

### Important Constraints

* This is NOT a SaaS platform.
* This is NOT a mobile app.
* This is NOT a microservice architecture.
* This is NOT Laravel.
* This is NOT WordPress.

Build everything using:

* Core PHP 8+
* MySQL
* Bootstrap 5
* JavaScript
* HTML5
* CSS3
* PDO for database operations
* MVC-like folder structure

The project must be deployable directly on standard shared hosting with:

* cPanel
* PHPMyAdmin
* Apache
* MySQL

No Node.js required.
No Docker required.
No Redis required.

---

# Project Name

**CAVA LMS Portal**

---

# UI Reference

Reference Website:

https://tagmango.com

### Design Requirements

* Modern LMS UI
* Clean white background
* Purple primary color theme
* Card-based course layout
* Fully responsive design
* Mobile friendly
* Dashboard inspired by TagMango
* Professional and premium appearance

---

# Features to Build

## Public Website

### Landing Page

Sections:

* Hero Banner
* About Section
* Featured Courses
* Upcoming Webinar
* Testimonials
* Contact Form
* Footer

---

## Authentication

### User Registration

Fields:

* Full Name
* Email
* Mobile Number
* Password

Requirements:

* Email must be unique
* Password must be hashed using password_hash()

### User Login

Fields:

* Email
* Password

Requirements:

* Session-based authentication

### Logout

* Destroy session securely

---

# User Dashboard

After login user should see:

* Welcome message
* Purchased Courses
* Upcoming Webinars
* My Profile
* Query History

---

# Course Management

Admin can:

* Create Course Categories
* Create Courses
* Upload Course Thumbnail
* Add Course Description

Example:

### Canada Immigration

Sub Courses:

* What is ECA
* What is EOI
* FSW Point System
* CRS Point System

---

# Course Videos

Admin can:

* Add Video Title
* Add Video Thumbnail
* Add Video URL

Supported Sources:

* YouTube Embed
* Vimeo Embed

Important:

* Do NOT implement custom video streaming.
* Store only external video URLs.

---

# Video Access Control

Rules:

### Purchased Course

* Show all course videos

### Not Purchased

* Show locked state
* Show Buy Course button

---

# Course Details Page

Display:

* Thumbnail
* Description
* Sections
* Video List
* Total Lessons

Design should be similar to TagMango course details page.

---

# Webinar Module

Admin can:

* Create Webinar
* Webinar Title
* Webinar Description
* Webinar Date
* Webinar Time
* Webinar Price

User can:

* Register Webinar
* Pay Webinar Fee

Example:

* Webinar Fee ₹99

---

# Payment Gateway

Integrate Razorpay.

Supported Payments:

* Course Purchase
* Webinar Purchase

Store:

* Payment ID
* Order ID
* Amount
* Status

Statuses:

* Pending
* Success
* Failed

---

# User Enrollments

After successful payment:

Automatically:

* Create Enrollment
* Unlock Purchased Course

---

# Query Management

User can submit queries.

Fields:

* Name
* Email
* Mobile Number
* Query Message

Admin can:

* View Queries
* Mark Query as Resolved

---

# Events Module

Admin can:

* Add Event
* Event Title
* Description
* Date
* Event Image

Users can:

* View Upcoming Events

---

# Admin Panel

Create separate Admin Panel.

Admin Login Required.

Modules:

* Dashboard
* Users
* Courses
* Categories
* Videos
* Webinars
* Enrollments
* Payments
* Queries
* Events
* Settings

---

# Admin Dashboard Statistics

Display:

* Total Users
* Total Courses
* Total Webinars
* Total Payments
* Total Revenue
* Recent Registrations
* Recent Payments

---

# Email Notifications

Use PHPMailer.

Send Emails For:

* User Registration
* Course Purchase
* Webinar Registration

---

# Database Design

Generate MySQL schema.

Tables:

* admins
* users
* categories
* courses
* course_sections
* course_videos
* enrollments
* webinars
* webinar_registrations
* payments
* queries
* events
* settings
* email_logs

---

# Security Requirements

Implement:

* PDO Prepared Statements
* Password Hashing
* Session Security
* Input Validation
* XSS Protection
* CSRF Protection
* File Upload Validation

---

# Recommended Folder Structure

/project-root

/admin

/assets

/css

/js

/images

/uploads

/config

/controllers

/models

/views

/includes

/vendor

/index.php

/login.php

/register.php

/dashboard.php

---

# Responsive Design Requirements

Must support:

* Desktop
* Tablet
* Mobile

---

# Coding Requirements

Generate:

* Complete MySQL Schema
* Folder Structure
* Config Files
* Database Connection Class
* Authentication System
* Admin Panel
* User Dashboard
* Razorpay Integration
* PHPMailer Integration
* CRUD Operations

Code must be:

* Clean
* Modular
* Reusable
* Production Ready

---

# Phase 1 Scope

Build ONLY:

✅ Landing Page

✅ Registration/Login

✅ User Dashboard

✅ Courses

✅ Course Videos

✅ Locked/Unlocked Courses

✅ Razorpay Payment Integration

✅ Webinar Registration

✅ Query Module

✅ Events Module

✅ Admin Panel

✅ Email Notifications

---

# Excluded Features

Do NOT build:

❌ OTP Login

❌ WhatsApp Integration

❌ CRM System

❌ Marketing Automation

❌ Mobile Application

❌ Zoom API Integration

❌ Multi-Vendor Features

❌ Advanced Analytics

---

# Development Sequence

Generate the project in the following order:

1. Database Schema
2. Folder Structure
3. Config Files
4. Authentication Module
5. Admin Panel
6. Course Module
7. Payment Module
8. Webinar Module
9. Email Notifications
10. Final Deployment Guide

The final solution should be fully deployable on standard shared hosting with cPanel and phpMyAdmin.
