
CREATE DATABASE HotelManagement CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE HotelManagement;

-- Bảng phòng
CREATE TABLE Room (
    RoomID INT AUTO_INCREMENT PRIMARY KEY,
    RoomName VARCHAR(50) NOT NULL,
    RoomType VARCHAR(50) NOT NULL,    -- đơn, đôi, VIP...
    Price DECIMAL(18,2) NOT NULL,
    Status VARCHAR(20) NOT NULL DEFAULT 'Trống'  -- Trống, Đang thuê, Đang dọn
);

-- Bảng khách hàng
CREATE TABLE Customer (
    CustomerID INT AUTO_INCREMENT PRIMARY KEY,
    FullName VARCHAR(100) NOT NULL,
    Phone VARCHAR(20),
    CCCD VARCHAR(20),
    --Address VARCHAR(200)
);

-- Bảng dịch vụ
CREATE TABLE Service (
    ServiceID INT AUTO_INCREMENT PRIMARY KEY,
    ServiceName VARCHAR(100) NOT NULL,
    Price DECIMAL(18,2) NOT NULL
);

-- Bảng đặt phòng
CREATE TABLE Booking (
    BookingID INT AUTO_INCREMENT PRIMARY KEY,
    RoomID INT NOT NULL,
    CustomerID INT NOT NULL,
    CheckInDate DATETIME NOT NULL,
    CheckOutDate DATETIME,
    TotalAmount DECIMAL(18,2) DEFAULT 0,
    Status VARCHAR(20) DEFAULT 'Đang ở',  -- Đang ở, Đã trả, Đã hủy
    FOREIGN KEY (RoomID) REFERENCES Room(RoomID),
    FOREIGN KEY (CustomerID) REFERENCES Customer(CustomerID)
);

-- Chi tiết dịch vụ của đặt phòng
CREATE TABLE BookingDetail (
    BookingID INT NOT NULL,
    ServiceID INT NOT NULL,
    Quantity INT NOT NULL DEFAULT 1,
    SubTotal DECIMAL(18,2) NOT NULL DEFAULT 0,
    PRIMARY KEY (BookingID, ServiceID),
    FOREIGN KEY (BookingID) REFERENCES Booking(BookingID),
    FOREIGN KEY (ServiceID) REFERENCES Service(ServiceID)
);

-- Hóa đơn
CREATE TABLE Invoice (
    InvoiceID INT AUTO_INCREMENT PRIMARY KEY,
    BookingID INT NOT NULL,
    InvoiceDate DATETIME DEFAULT NOW(),
    TotalAmount DECIMAL(18,2),
    FOREIGN KEY (BookingID) REFERENCES Booking(BookingID)
);

-- Bảng người dùng
CREATE TABLE Users (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(50) NOT NULL UNIQUE,
    PasswordHash VARCHAR(255) NOT NULL,
    FullName VARCHAR(100),
    Role VARCHAR(20) NOT NULL DEFAULT 'Customer',  -- 'Admin' hoặc 'Customer'
    CreatedAt DATETIME DEFAULT NOW(),
    Email VARCHAR(100)
);


INSERT INTO Users (Username, PasswordHash, FullName, Role)
VALUES ('admin', 'admin123', 'Quản trị viên', 'Admin');

INSERT INTO Room (RoomName, RoomType, Price, Status)
VALUES ('101', 'Đơn', 300000, 'Trống'),
       ('102', 'Đôi', 450000, 'Trống'),
       ('201', 'VIP', 800000, 'Trống');
       
       USE HotelManagement;

ALTER TABLE Customer
ADD COLUMN UserID INT UNIQUE,
ADD CONSTRAINT FK_Customer_User
    FOREIGN KEY (UserID) REFERENCES Users(UserID);
    
    USE HotelManagement;
