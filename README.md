#  Student Skill Exchange Platform

A web-based **Student Skill Exchange Platform** that allows students to share, learn, and exchange skills with each other. The platform provides separate dashboards and functionalities for **Skill Providers, Skill Seekers, and Administrators**.

Students can publish their skills, search for skills offered by others, send exchange requests, communicate through chat, manage assignments, submit work, and provide ratings and reviews after completing an exchange.

---

##  Project Overview

The **Student Skill Exchange Platform** is designed to create a collaborative environment where students can exchange knowledge and skills.

For example:

> A student who knows **Python** can offer Python lessons, while another student interested in learning Python can send an exchange request.

The system manages the complete exchange process:

**Registration → Login → Browse Skills → Send Request → Accept/Decline → Communication → Assignment → Submission → Completion → Review & Rating**

---

##  Main Features

###  Authentication & User Management

* User registration
* Secure login and logout
* Password hashing using PHP `password_hash()` and `password_verify()`
* Session-based authentication
* Role-based access control
* Profile management
* Change password
* Account deletion

###  Skill Provider

Skill Providers can:

* Create and manage their skills
* Specify proficiency levels
* View their offered skills
* Monitor incoming exchange requests
* Accept or decline requests
* Mark exchanges as completed
* Create assignments for Skill Seekers
* Track assignments and submissions
* Communicate with Skill Seekers through chat

###  Skill Seeker

Skill Seekers can:

* Browse available skills
* Search for specific skills
* View skill providers
* Send exchange requests
* Track request status
* Communicate with Skill Providers
* View assigned tasks
* Submit assignment files
* Track submission status
* Review completed exchanges
* Give ratings and written feedback

###  Exchange Chat

The platform includes a communication system that allows users involved in an exchange to:

* Send messages
* Receive messages
* View previous messages
* Communicate based on a specific exchange request

###  Assignment Management

Skill Providers can create assignments associated with an exchange.

Assignments include:

* Assignment title
* Description
* Deadline
* Attached files
* Submission tracking

Skill Seekers can:

* View assignments
* Download/view assignment information
* Upload their submissions
* Submit work before or after the deadline

The system automatically tracks whether a submission is:

* **On Time**
* **Late**

###  Reviews & Ratings

After completing an exchange, users can provide:

* 1–5 star ratings
* Written reviews

This helps users evaluate their exchange experience and provides feedback about Skill Providers.

###  Admin Panel

Administrators can manage and monitor the platform.

Admin features include:

* Admin dashboard
* User management
* Role management
* Exchange monitoring
* Exchange status filtering
* Platform reports
* User statistics
* Request statistics
* Top-rated providers
* Most-requested skills

---

##  Technologies Used

| Technology     | Purpose                          |
| -------------- | -------------------------------- |
| **HTML5**      | Web page structure               |
| **CSS3**       | Styling and responsive interface |
| **JavaScript** | Client-side functionality        |
| **PHP**        | Server-side/backend development  |
| **MySQL**      | Database management              |
| **Apache**     | Web server                       |
| **XAMPP**      | Local development environment    |
| **phpMyAdmin** | Database administration          |

---

##  Project Structure

```text
skill-exchange-platform/
│
├── admin/
│   ├── dashboard.php
│   ├── exchanges.php
│   ├── reports.php
│   └── users.php
│
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
│
├── config/
│   └── db.php
│
├── includes/
│   ├── auth.php
│   ├── footer.php
│   ├── functions.php
│   └── header.php
│
├── provider/
│   ├── add_skill.php
│   ├── assignments.php
│   ├── dashboard.php
│   ├── requests.php
│   └── skills.php
│
├── seeker/
│   ├── assignments.php
│   ├── browse_skills.php
│   ├── dashboard.php
│   ├── my_requests.php
│   └── review.php
│
├── sql/
│   ├── schema.sql
│   └── assignment_migration.sql
│
├── chat.php
├── dashboard.php
├── index.php
├── login.php
├── logout.php
├── profile.php
├── register.php
└── routine.php
```

---

##  Database

The project uses a MySQL database named:

```text
skill_exchange
```

### Main Database Tables

* `roles`
* `users`
* `userAvailability`
* `skills`
* `exchangeRequests`
* `exchangeMessages`
* `assignments`
* `assignmentSubmissions`
* `exchangeReviews`

### Database Relationships

The database manages relationships between:

```text
Users
  │
  ├── Skills
  │
  ├── Exchange Requests
  │       │
  │       ├── Messages
  │       └── Assignments
  │               └── Submissions
  │
  └── Reviews
```

Foreign keys and cascading rules are used to maintain database consistency.

---

##  Security Features

The project includes several basic security practices:

* Password hashing
* Session-based authentication
* Role-based authorization
* Prepared SQL statements
* Input validation
* Protected role-specific pages
* Database foreign-key constraints

SQL queries use **prepared statements** to reduce the risk of SQL injection.

---

#  Installation & Setup

## 1. Install XAMPP

Install **XAMPP** and make sure Apache and MySQL are available.

Start:

```text
Apache
MySQL
```

---

## 2. Clone or Download the Project

Place the project inside the XAMPP `htdocs` directory:

```text
C:\xampp\htdocs\
```

The final structure should look like:

```text
C:\xampp\htdocs\skill-exchange-platform\
```

---

## 3. Create the Database

Open:

```text
http://localhost/phpmyadmin
```

Import the following SQL file:

```text
sql/schema.sql
```

This will create the:

```text
skill_exchange
```

database along with the required tables and default roles.

---

## 4. Configure Database Connection

Open:

```text
config/db.php
```

Configure the database credentials according to your local MySQL setup.

For a standard XAMPP installation, the default configuration is generally:

```text
Host: localhost
Username: root
Password: 
Database: skill_exchange
```

---

## 5. Run the Project

Open your browser and visit:

```text
http://localhost/skill-exchange-platform/
```

---

# 👤 User Roles

The system contains three major roles.

### Skill Provider

```text
Register
   ↓
Login
   ↓
Provider Dashboard
   ↓
Add Skills
   ↓
Receive Exchange Request
   ↓
Accept / Decline
   ↓
Communicate
   ↓
Create Assignment
   ↓
Complete Exchange
```

### Skill Seeker

```text
Register
   ↓
Login
   ↓
Seeker Dashboard
   ↓
Browse Skills
   ↓
Send Exchange Request
   ↓
Communicate with Provider
   ↓
Complete Assignment
   ↓
Submit Work
   ↓
Review & Rating
```

### Administrator

```text
Admin Login
    ↓
Admin Dashboard
    ├── Manage Users
    ├── Monitor Exchanges
    ├── View Reports
    └── Monitor Platform Statistics
```

---

#  Creating an Admin Account

Admin registration is not available through the public registration form.

To create an administrator:

1. Register a normal account.
2. Open phpMyAdmin.
3. Select the `skill_exchange` database.
4. Open the `users` table.
5. Change the user's `roleId` to:

```text
3
```

The default roles are:

```text
1 → Skill Provider
2 → Skill Seeker
3 → Admin
```

Alternatively, run:

```sql
UPDATE users
SET roleId = 3
WHERE email = 'your-email@example.com';
```

Then log out and log back in.

---

#  Exchange Workflow

The complete exchange workflow is:

```text
User Registration
       ↓
      Login
       ↓
Browse Available Skills
       ↓
Send Exchange Request
       ↓
Provider Reviews Request
       ↓
 ┌───────────────┐
 │               │
Accept         Decline
 │
 ↓
Exchange Starts
 │
 ↓
Communication / Chat
 │
 ↓
Assignment
 │
 ↓
Submission
 │
 ↓
Exchange Completed
 │
 ↓
Rating & Review
```

---

#  Admin Reports

The administration panel provides information such as:

* Total users
* Users by role
* Exchange requests by status
* Top-rated providers
* Most-requested skills
* Exchange monitoring

These features allow administrators to monitor the overall activity of the platform.

---

#  Important Files

| File                     | Purpose                          |
| ------------------------ | -------------------------------- |
| `index.php`              | Landing page                     |
| `register.php`           | User registration                |
| `login.php`              | User authentication              |
| `dashboard.php`          | General dashboard routing        |
| `profile.php`            | User profile management          |
| `chat.php`               | Exchange communication           |
| `config/db.php`          | Database connection              |
| `includes/auth.php`      | Authentication and authorization |
| `includes/functions.php` | Common helper functions          |
| `provider/`              | Provider functionality           |
| `seeker/`                | Seeker functionality             |
| `admin/`                 | Administration functionality     |
| `sql/schema.sql`         | Main database schema             |

---

#  Project Objectives

The main objectives of this project are to:

* Build a centralized platform for student skill exchange
* Connect students who want to teach with students who want to learn
* Simplify the skill exchange process
* Provide secure user authentication
* Implement role-based access control
* Enable communication between users
* Support assignment-based skill exchange
* Track exchange progress
* Provide ratings and reviews
* Allow administrators to monitor platform activities

---

#  Future Improvements

Possible future improvements include:

* Email notifications
* Real-time chat using WebSockets
* Advanced skill recommendation
* Skill categories and filtering
* Improved search functionality
* Profile pictures
* Notification system
* Calendar integration
* More detailed analytics and charts
* Mobile-friendly/PWA version
* Certificate generation after successful exchanges

---

#  Project Information

**Project:** Student Skill Exchange Platform

**Course:** CSC 3215 — Web Technologies

**Type:** Web-Based Application

**Backend:** PHP

**Database:** MySQL

**Development Environment:** XAMPP

---

##  License

This project was developed for educational and academic purposes.
