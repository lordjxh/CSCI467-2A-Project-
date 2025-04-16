-- as orders are paid this table will be updated to release old IDs

CREATE TABLE Users (
    userID INT AUTO_INCREMENT PRIMARY KEY
);

CREATE TABLE UserAccount (
    userID INT PRIMARY KEY,
    firstName VARCHAR(25),
    lastName VARCHAR (25),
    shippingAddress VARCHAR(30),
    state VARCHAR(20),
    zipcode INT,
    phone INT,
    email VARCHAR(30),
    userPassword VARCHAR(30)
);

CREATE TABLE PaymentData (
    userID INT,
    cardNumber INT,
    expiration INT,
    cvv INT,
    FOREIGN KEY (userID) REFERENCES UserAccount(userID)
);

-- because account status is unknown to this table, it pulls IDs from both user tables and will
-- programmatically ignore one or the other based on its state (null/not null)

CREATE TABLE CustomerCart (
    productID INT,
    userID INT,
    userAccID INT,
    FOREIGN KEY (productID) REFERENCES Products(productID),
    FOREIGN KEY (userID) REFERENCES Users(userID),
    FOREIGN KEY (userAccID) REFERENCES UserAccount(userID)
);

-- connection to LegacyDB will done programmatically

CREATE TABLE Products (
    productID INT,
    quantity INT PRIMARY KEY,
    FOREIGN KEY (productID) REFERENCES LegacyDB(productID)
);

CREATE TABLE InvoiceDB (
    invoiceNO INT PRIMARY KEY,
    datePaid INT,
    authorizationNO INT,
    fulfillmentStatus CHAR(1),
    productID INT,
    shippingFlag CHAR(1),
    FOREIGN KEY (productID) REFERENCES LegacyDB(productID),
    FOREIGN KEY (shippingFlag) REFERENCES AdminDB(shippingFlag)
);

CREATE TABLE WarehouseDB (
    fulfillmentStatus CHAR(1),
    productID INT,
    invoiceNO INT,
    quantity INT,
    FOREIGN KEY (productID) REFERENCES LegacyDB(productID),
    FOREIGN KEY (invoiceNO) REFERENCES InvoiceDB(invoiceNO),
    FOREIGN KEY (quantity) REFERENCES Products(quantity)
);

CREATE TABLE AdminDB (
    shippingFlag CHAR(1) PRIMARY KEY,
    weightCharges INT,
    shippingCharges INT,
    invoiceNO INT,
    FOREIGN KEY (invoiceNO) REFERENCES InvoiceDB(invoiceNO)
);