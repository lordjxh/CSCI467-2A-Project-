--Users - a table that contains temporary user ID's for single instance users.
-- as orders are paid this table will be updated to release old IDs
CREATE TABLE Users (
    userID INT AUTO_INCREMENT PRIMARY KEY
) AUTO_INCREMENT = 101;

--UserAccount - for returning users, allows storing user's information
CREATE TABLE UserAccount (
    userID INT AUTO_INCREMENT PRIMARY KEY,
    firstName VARCHAR(25),
    lastName VARCHAR (25),
    shippingAddress VARCHAR(30),
    state VARCHAR(2),
    zipcode INT,
    phone VARCHAR(10),
    email VARCHAR(30),
    userPassword VARCHAR(30)
) AUTO_INCREMENT = 10001;

--PaymentData - an optional table for users that wish to store their payment info for faster checkout
CREATE TABLE PaymentData (
    userID INT PRIMARY KEY,
    cardNumber INT,
    expiration INT,
    cvv INT,
    FOREIGN KEY (userID) REFERENCES UserAccount(userID)
);

-- because account status is unknown to this table, it pulls IDs from both user tables and will
-- programmatically ignore one or the other based on its state (null/not null)

--Products - contains information about products being sold. Also stores inventory information.
-- connection to LegacyDB will done programmatically, and store the legacyID for cross-reference
CREATE TABLE Products (
    productID INT AUTO_INCREMENT PRIMARY KEY,
    storeQuantity INT, -- online count seen to users, updates after a successful checkout
    warehouseQuantity INT, -- actual count of item in hand, updates from WarehouseDB changes
    legacyID INT -- productID != legacyID, there are gaps
);

--CustomerCart - as users shop, this table will store the cart contents
CREATE TABLE CustomerCart (
    cartID INT AUTO_INCREMENT PRIMARY KEY,
    productID INT,
    userID INT,
    userAccID INT,
    quantity INT,

    FOREIGN KEY (productID) REFERENCES Products(productID),
    FOREIGN KEY (userID) REFERENCES Users(userID),
    FOREIGN KEY (userAccID) REFERENCES UserAccount(userID)
);

--InvoiceDB - stores all invoices for orders following checkout. Additionally if a user has
--an account, creates a foreign key reference to the account for easier lookup
CREATE TABLE InvoiceDB (
    invoiceNO INT AUTO_INCREMENT PRIMARY KEY,
    userID INT,
    subtotal DOUBLE,
    shippingCost DOUBLE,
    grandTotal DOUBLE,
    datePaid DATETIME,
    authorizationNO INT,
    fulfillmentStatus CHAR(1),
    shippingFlag CHAR(1),

    FOREIGN KEY (userID) REFERENCES UserAccount(userID)
) AUTO_INCREMENT = 10001;

--Purchases - separates from CustomerCart, when a purchase is made, moves and assigns all cart items to the invoiceNO
CREATE TABLE Purchases (
    purchaseID INT PRIMARY KEY,
    invoiceNO INT,
    productID INT,
    quantity INT,

    FOREIGN KEY (invoiceNO) REFERENCES InvoiceDB(invoiceNO),
    FOREIGN KEY (productID) REFERENCES Products(productID)
);

--ShippingInfo - holds information regarding shipping details for an invoice
CREATE TABLE ShippingInfo (
    invoiceNO INT PRIMARY KEY,
    shippingFirstName VARCHAR(32),
    shippingLastName VARCHAR(32),
    shippingAddress VARCHAR(32),
    shippingCity VARCHAR(32),
    shippingState VARCHAR(2),
    shippingZipcode VARCHAR(9),
    shippingEmail VARCHAR(32),
    shippingPhone VARCHAR(12),

    FOREIGN KEY (invoiceNO) REFERENCES InvoiceDB(invoiceNO)
);

--BillingInfo - holds information regarding billing details for an invoice
CREATE TABLE BillingInfo (
    invoiceNO INT PRIMARY KEY,
    billingFirstName VARCHAR(32),
    billingLastName VARCHAR(32),
    billingAddress VARCHAR(32),
    billingCity VARCHAR(32),
    billingState VARCHAR(2),
    billingZipcode VARCHAR(9),
    billingEmail VARCHAR(32),
    billingPhone VARCHAR(12),

    FOREIGN KEY (invoiceNO) REFERENCES InvoiceDB(invoiceNO)
);

--WarehouseDB - for warehouse workers, holds information about products that are
--successfully shipped
CREATE TABLE WarehouseDB (
    invoiceNO INT PRIMARY KEY,
    fulfillmentStatus CHAR(1),
    productID INT,
    quantityShipped INT,

    FOREIGN KEY (invoiceNO) REFERENCES InvoiceDB(invoiceNO),
    FOREIGN KEY (productID) REFERENCES Products(productID)
);

--AdminDB - for administrators, can set weight constraints for determining shipping costs
    --NOTE: I don't think this table reflects what/how the AdminDB should be utilized. Shipping should
    --be something already calculated during checkout, not afterwards
CREATE TABLE AdminDB (
    invoiceNO INT PRIMARY KEY,
    shippingFlag CHAR(1),
    weightCharges INT,
    shippingCharges INT,

    FOREIGN KEY (invoiceNO) REFERENCES InvoiceDB(invoiceNO)
);

--ShippingWeights - allows admins to change shipping costs based on a weight range. Assumes a percentage
--of the total cost of the order
CREATE TABLE ShippingWeights (
    weightID INT PRIMARY KEY,
    minimumWeight DOUBLE,
    maximumWeight DOUBLE,
    shippingPercent DECIMAL
);