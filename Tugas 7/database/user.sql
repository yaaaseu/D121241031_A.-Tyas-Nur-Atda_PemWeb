CREATE USER 'guestbook_app'@'localhost'
    IDENTIFIED BY 'GantiDenganPasswordKuat';

GRANT SELECT, INSERT
    ON perpustakaan_db.buku_tamu
    TO 'guestbook_app'@'localhost';

FLUSH PRIVILEGES;