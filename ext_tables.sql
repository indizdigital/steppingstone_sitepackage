CREATE TABLE tt_content (
    header_style varchar(255) DEFAULT '' NOT NULL
);
CREATE TABLE tx_steppingstonesitepackage_form_submissions (
    company varchar(255) DEFAULT '' NOT NULL,
    title varchar(50) DEFAULT '' NOT NULL,
    first_name varchar(255) DEFAULT '' NOT NULL,
    last_name varchar(255) DEFAULT '' NOT NULL,
    address_line_1 varchar(255) DEFAULT '' NOT NULL,
    address_line_2 varchar(255) DEFAULT '',
    postcode varchar(10) DEFAULT '' NOT NULL,
    location varchar(255) DEFAULT '' NOT NULL,
    country varchar(255) DEFAULT '' NOT NULL,
    phone varchar(100) DEFAULT '',
    email varchar(255) DEFAULT '' NOT NULL,
    terms_accepted tinyint(1) DEFAULT 0 NOT NULL,
    newsletter tinyint(1) DEFAULT 0 NOT NULL,
    message text
);
