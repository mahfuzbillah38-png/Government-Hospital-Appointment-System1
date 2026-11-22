-- Government Hospital Appointment System (XAMPP / phpMyAdmin)
CREATE DATABASE IF NOT EXISTS hospital_db;
USE hospital_db;

CREATE TABLE departments (
  dept_id INT AUTO_INCREMENT PRIMARY KEY,
  dept_name VARCHAR(60) NOT NULL UNIQUE
);

CREATE TABLE doctors (
  doctor_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  dept_id INT NOT NULL,
  qualification VARCHAR(100),
  room_no VARCHAR(10),
  visiting_days VARCHAR(60),
  FOREIGN KEY (dept_id) REFERENCES departments(dept_id)
);

-- Fixed time slots offered for every doctor
CREATE TABLE time_slots (
  slot_id INT AUTO_INCREMENT PRIMARY KEY,
  slot_time TIME NOT NULL UNIQUE
);

CREATE TABLE appointments (
  appt_id INT AUTO_INCREMENT PRIMARY KEY,
  token_no VARCHAR(20) UNIQUE,
  patient_name VARCHAR(80) NOT NULL,
  age INT NOT NULL,
  gender ENUM('Male','Female','Other') NOT NULL,
  phone VARCHAR(15) NOT NULL,
  doctor_id INT NOT NULL,
  appt_date DATE NOT NULL,
  slot_id INT NOT NULL,
  problem VARCHAR(200),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id),
  FOREIGN KEY (slot_id) REFERENCES time_slots(slot_id),
  UNIQUE (doctor_id, appt_date, slot_id)   -- prevents double booking
);

INSERT INTO departments (dept_name) VALUES
('Medicine'),('Cardiology'),('Pediatrics'),('Orthopedics'),('Gynecology'),('ENT');

INSERT INTO doctors (name, dept_id, qualification, room_no, visiting_days) VALUES
('Dr. Rahim Uddin',1,'MBBS, FCPS (Medicine)','101','Sat - Wed'),
('Dr. Nasrin Akter',2,'MBBS, MD (Cardiology)','204','Sun - Thu'),
('Dr. Kamal Hossain',3,'MBBS, DCH','112','Sat - Thu'),
('Dr. Farhana Islam',4,'MBBS, MS (Orthopedics)','305','Sun, Tue, Thu'),
('Dr. Shirin Sultana',5,'MBBS, FCPS (Gynecology)','118','Sat - Wed'),
('Dr. Abdul Karim',6,'MBBS, DLO','122','Sun - Thu'),
('Dr. Tahmina Begum',1,'MBBS, MD (Medicine)','103','Sat - Wed');

INSERT INTO time_slots (slot_time) VALUES
('09:00:00'),('09:30:00'),('10:00:00'),('10:30:00'),('11:00:00'),
('11:30:00'),('12:00:00'),('12:30:00');
