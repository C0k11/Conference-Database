-- ==============================================================================
-- CISC332 Conference Database Implementation
-- ER-to-Relational Schema Mapping
-- ==============================================================================

drop database if exists conferenceDB;
create database conferenceDB;
use conferenceDB;

-- ------------------------------------------------------------------------------
-- 1. Create Independent Strong Entities (No Foreign Keys)
-- ------------------------------------------------------------------------------

-- Company Table (Derived attribute EmailsSent is omitted per standard design)
CREATE TABLE Company (
    CompName VARCHAR(100) PRIMARY KEY,
    SponsorLevel VARCHAR(50) NOT NULL,
    PromoLimit INT NOT NULL
);

CREATE TABLE HotelRoom (
    RoomNumber INT PRIMARY KEY,
    BedCount INT NOT NULL
);

CREATE TABLE Session (
    sessID INT PRIMARY KEY,
    sessTitle VARCHAR(150) NOT NULL,
    sessDate DATE NOT NULL,
    sessLoc VARCHAR(100) NOT NULL,
    sessStart TIME NOT NULL,
    sessEnd TIME NOT NULL
);

CREATE TABLE CommMember (
    MemberID INT PRIMARY KEY,
    MemName VARCHAR(100) NOT NULL
);

CREATE TABLE Attendee (
    AttendeeID INT PRIMARY KEY,
    AttendeeName VARCHAR(100) NOT NULL
);

-- ------------------------------------------------------------------------------
-- 2. Create Dependent Entities & Relationships (Contains Foreign Keys)
-- ------------------------------------------------------------------------------

-- SubCommittee (1:1 with CommMember for Chairs, total participation)
CREATE TABLE SubCommittee (
    ComName VARCHAR(100) PRIMARY KEY,
    subDesc VARCHAR(255),
    Chair_MemberID INT NOT NULL,
    FOREIGN KEY (Chair_MemberID) REFERENCES CommMember(MemberID) 
        ON DELETE RESTRICT 
        ON UPDATE CASCADE
);

-- Committee_Members (M:N relationship Is_Member)
CREATE TABLE Committee_Members (
    ComName VARCHAR(100),
    MemberID INT,
    PRIMARY KEY (ComName, MemberID),
    FOREIGN KEY (ComName) REFERENCES SubCommittee(ComName) ON DELETE CASCADE,
    FOREIGN KEY (MemberID) REFERENCES CommMember(MemberID) ON DELETE CASCADE
);

-- JobAd (1:N from Company)
CREATE TABLE JobAd (
    JobID INT PRIMARY KEY,
    JobTitle VARCHAR(150) NOT NULL,
    JobCity VARCHAR(100) NOT NULL,
    JobProv VARCHAR(50) NOT NULL,
    JobPay DECIMAL(10,2) NOT NULL,
    CompName VARCHAR(100) NOT NULL,
    FOREIGN KEY (CompName) REFERENCES Company(CompName) ON DELETE CASCADE
);

-- SentEmail (Weak Entity dependent on Company)
CREATE TABLE SentEmail (
    CompName VARCHAR(100),
    Subject VARCHAR(150),
    EmailDate DATE,
    PRIMARY KEY (CompName, Subject, EmailDate),
    FOREIGN KEY (CompName) REFERENCES Company(CompName) ON DELETE CASCADE
);

-- ------------------------------------------------------------------------------
-- 3. Create Subclasses (ISA Relationship from Attendee)
-- ------------------------------------------------------------------------------

-- Student (1:N from HotelRoom. Assigned is partial for Student -> RoomNumber can be NULL)
CREATE TABLE Student (
    AttendeeID INT PRIMARY KEY,
    Fee DECIMAL(5,2) DEFAULT 50.00 NOT NULL,
    RoomNumber INT,
    FOREIGN KEY (AttendeeID) REFERENCES Attendee(AttendeeID) ON DELETE CASCADE,
    FOREIGN KEY (RoomNumber) REFERENCES HotelRoom(RoomNumber) ON DELETE SET NULL
);

-- Professional
CREATE TABLE Professional (
    AttendeeID INT PRIMARY KEY,
    Fee DECIMAL(5,2) DEFAULT 100.00 NOT NULL,
    FOREIGN KEY (AttendeeID) REFERENCES Attendee(AttendeeID) ON DELETE CASCADE
);

-- SponsorRep (1:N from Company. Total participation -> CompName cannot be NULL)
CREATE TABLE SponsorRep (
    AttendeeID INT PRIMARY KEY,
    CompName VARCHAR(100) NOT NULL,
    FOREIGN KEY (AttendeeID) REFERENCES Attendee(AttendeeID) ON DELETE CASCADE,
    FOREIGN KEY (CompName) REFERENCES Company(CompName) ON DELETE CASCADE
);

-- ------------------------------------------------------------------------------
-- 4. Create Remaining M:N Relationships
-- ------------------------------------------------------------------------------

-- Speaks (M:N between Attendee and Session)
CREATE TABLE Speaks (
    AttendeeID INT,
    sessID INT,
    PRIMARY KEY (AttendeeID, sessID),
    FOREIGN KEY (AttendeeID) REFERENCES Attendee(AttendeeID) ON DELETE CASCADE,
    FOREIGN KEY (sessID) REFERENCES Session(sessID) ON DELETE CASCADE
);

-- ==============================================================================
-- POPULATING THE DATABASE (Min. 6 tuples per table)
-- Insert order strictly follows parent-child dependencies to avoid FK errors
-- ==============================================================================

-- 1. Insert Companies
INSERT INTO Company (CompName, SponsorLevel, PromoLimit) VALUES 
('Google', 'Platinum', 5),
('Microsoft', 'Gold', 4),
('Amazon', 'Silver', 3),
('Apple', 'Platinum', 5),
('Meta', 'Bronze', 0),
('IBM', 'Gold', 4);

-- 2. Insert Hotel Rooms
INSERT INTO HotelRoom (RoomNumber, BedCount) VALUES 
(101, 1), (102, 2), (103, 2), (104, 1), (105, 2), (106, 1);

-- 3. Insert Sessions
INSERT INTO Session (sessID, sessTitle, sessDate, sessLoc, sessStart, sessEnd) VALUES 
(1, 'Future of AI', '2026-05-10', 'Hall A', '09:00:00', '10:30:00'),
(2, 'Cloud Computing Basics', '2026-05-10', 'Room 101', '11:00:00', '12:00:00'),
(3, 'Quantum Supremacy', '2026-05-10', 'Hall B', '13:00:00', '14:30:00'),
(4, 'Cybersecurity in 2026', '2026-05-11', 'Room 202', '09:00:00', '10:30:00'),
(5, 'UI/UX Design Trends', '2026-05-11', 'Hall A', '11:00:00', '12:00:00'),
(6, 'Database Optimization', '2026-05-11', 'Room 101', '14:00:00', '15:30:00');

-- 4. Insert CommMembers
INSERT INTO CommMember (MemberID, MemName) VALUES 
(1, 'Alice Smith'), (2, 'Bob Johnson'), (3, 'Charlie Brown'), 
(4, 'Diana Prince'), (5, 'Evan Wright'), (6, 'Fiona Clark');

-- 5. Insert SubCommittees
INSERT INTO SubCommittee (ComName, subDesc, Chair_MemberID) VALUES 
('Program', 'Plans conference sessions', 1),
('Registration', 'Handles attendee check-in', 2),
('Sponsorship', 'Liaises with corporate sponsors', 3),
('Catering', 'Organizes food and beverages', 4),
('Logistics', 'Manages physical venue requirements', 5),
('Publicity', 'Marketing and social media', 6);

-- 6. Insert Committee_Members (Is_Member)
INSERT INTO Committee_Members (ComName, MemberID) VALUES 
('Program', 1), ('Program', 2), ('Registration', 2), 
('Sponsorship', 3), ('Catering', 4), ('Logistics', 5), 
('Publicity', 6), ('Publicity', 1);

-- 7. Insert Attendees (Total: 18)
INSERT INTO Attendee (AttendeeID, AttendeeName) VALUES 
(1, 'Student One'), (2, 'Student Two'), (3, 'Student Three'), 
(4, 'Student Four'), (5, 'Student Five'), (6, 'Student Six'),
(7, 'Prof Alpha'), (8, 'Prof Beta'), (9, 'Prof Gamma'), 
(10, 'Prof Delta'), (11, 'Prof Epsilon'), (12, 'Prof Zeta'),
(13, 'Rep Alice'), (14, 'Rep Bob'), (15, 'Rep Charlie'), 
(16, 'Rep Dave'), (17, 'Rep Eve'), (18, 'Rep Frank');

-- 8. Insert Students (IDs 1-6)
INSERT INTO Student (AttendeeID, Fee, RoomNumber) VALUES 
(1, 50.00, 101), (2, 50.00, 102), (3, 50.00, 102), 
(4, 50.00, 103), (5, 50.00, NULL), (6, 50.00, NULL); -- Two students not requiring rooms

-- 9. Insert Professionals (IDs 7-12)
INSERT INTO Professional (AttendeeID, Fee) VALUES 
(7, 100.00), (8, 100.00), (9, 100.00), 
(10, 100.00), (11, 100.00), (12, 100.00);

-- 10. Insert SponsorReps (IDs 13-18)
INSERT INTO SponsorRep (AttendeeID, CompName) VALUES 
(13, 'Google'), (14, 'Google'), (15, 'Microsoft'), 
(16, 'Amazon'), (17, 'Apple'), (18, 'IBM');

-- 11. Insert JobAds
INSERT INTO JobAd (JobID, JobTitle, JobCity, JobProv, JobPay, CompName) VALUES 
(1, 'Senior SWE', 'Toronto', 'ON', 120000.00, 'Google'),
(2, 'Cloud Architect', 'Vancouver', 'BC', 130000.00, 'Microsoft'),
(3, 'Data Scientist', 'Montreal', 'QC', 110000.00, 'Amazon'),
(4, 'Hardware Engineer', 'Toronto', 'ON', 115000.00, 'Apple'),
(5, 'Frontend Developer', 'Toronto', 'ON', 90000.00, 'Meta'),
(6, 'Database Admin', 'Ottawa', 'ON', 95000.00, 'IBM');

-- 12. Insert SentEmails (Weak Entity)
INSERT INTO SentEmail (CompName, Subject, EmailDate) VALUES 
('Google', 'Join our AI team!', '2026-04-01'),
('Google', 'Google I/O 2026 Invites', '2026-04-15'),
('Microsoft', 'Azure Cloud Credits for Students', '2026-04-10'),
('Amazon', 'AWS Certification Pathways', '2026-04-20'),
('Apple', 'WWDC 2026 Announcement', '2026-04-05'),
('IBM', 'Quantum Computing Webinar', '2026-04-25');

-- 13. Insert Speaks
INSERT INTO Speaks (AttendeeID, sessID) VALUES 
(7, 1), (8, 2), (9, 3), 
(10, 4), (11, 5), (12, 6);