<?PHP

/**
        * Check for database configuration file.
        * If it exists, proceed to login page.
        * If it doesn't, call the setup page.
        */
        
        require_once 'functions.php';

        // Automatic setup check
        try {
            $db = connect();
            $db->query("SELECT 1 FROM setting LIMIT 1");
            header('Location: login.php');
            exit();
        } catch (Exception $e) {
            // Database not initialized, redirect to auto-import if environment variables exist
            if (getenv('MYSQL_HOST') && getenv('MYSQL_USER') && getenv('MYSQL_DATABASE')) {
                session_start();
                $_SESSION['db_host'] = getenv('MYSQL_HOST');
                $_SESSION['db_user'] = getenv('MYSQL_USER');
                $_SESSION['db_pass'] = getenv('MYSQL_PASSWORD');
                $_SESSION['db_name'] = getenv('MYSQL_DATABASE');
                $_SESSION['db_type'] = 1; // Fresh install
                header('Location: setup_dbimport.php');
                exit();
            }
        }

        if(file_exists('config/config.php')) header('Location: login.php');
        
        else header('Location: setup.php');

?>