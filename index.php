<?php

// =============================================================================
// Simple PHP CMS
// =============================================================================

// -----------------------------------------------------------------------------
// Configuration
// -----------------------------------------------------------------------------

// The title of your website
define('SITE_TITLE', 'My Simple CMS');

// The folder where your pages are stored
define('CONTENT_DIR', 'content');

// The default page to display
define('DEFAULT_PAGE', 'home');

// The password to access the admin panel
define('ADMIN_PASSWORD', 'password');

// -----------------------------------------------------------------------------
// Core CMS Class
// -----------------------------------------------------------------------------

class SimpleCMS
{
    // The content of the current page
    public $content;

    // The title of the current page
    public $title;

    // The name of the current page
    public $page;

    public function __construct()
    {
        // Get the current page from the URL
        $this->page = isset($_GET['page']) ? $_GET['page'] : DEFAULT_PAGE;

        // Load the content of the current page
        $this->loadContent();
    }

    // Load the content of the current page
    public function loadContent()
    {
        $file = CONTENT_DIR . '/' . $this->page . '.txt';

        if (file_exists($file)) {
            $this->content = file_get_contents($file);
            $this->title = ucwords(str_replace('-', ' ', $this->page));
        } else {
            $this->content = 'Page not found.';
            $this->title = 'Page Not Found';
        }
    }

    // Save the content of the current page
    public function saveContent()
    {
        if (isset($_POST['content'])) {
            file_put_contents(CONTENT_DIR . '/' . $this->page . '.txt', $_POST['content']);
            header('Location: ?page=' . $this->page);
        }
    }

    // Delete the current page
    public function deletePage()
    {
        if (isset($_GET['delete'])) {
            unlink(CONTENT_DIR . '/' . $_GET['delete'] . '.txt');
            header('Location: ?');
        }
    }

    // Get a list of all pages
    public function getPages()
    {
        $pages = [];
        $files = glob(CONTENT_DIR . '/*.txt');

        foreach ($files as $file) {
            $pages[] = basename($file, '.txt');
        }

        return $pages;
    }
}

// -----------------------------------------------------------------------------
// Admin Panel
// -----------------------------------------------------------------------------

session_start();

if (isset($_GET['admin'])) {
    // Handle login
    if (isset($_POST['password'])) {
        if ($_POST['password'] === ADMIN_PASSWORD) {
            $_SESSION['admin'] = true;
        }
    }

    // Handle logout
    if (isset($_GET['logout'])) {
        unset($_SESSION['admin']);
        header('Location: ?');
    }

    // If logged in, show the admin panel
    if (isset($_SESSION['admin'])) {
        $cms = new SimpleCMS();
        $cms->saveContent();
        $cms->deletePage();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Admin Panel</title>
            <link rel="stylesheet" href="https://unpkg.com/sakura.css/css/sakura.css" type="text/css">
        </head>
        <body>
            <h1>Admin Panel</h1>
            <p><a href="?admin&logout">Logout</a></p>
            <hr>
            <h2>Pages</h2>
            <ul>
                <?php foreach ($cms->getPages() as $page): ?>
                    <li>
                        <a href="?page=<?php echo $page; ?>"><?php echo ucwords(str_replace('-', ' ', $page)); ?></a>
                        - <a href="?admin&edit=<?php echo $page; ?>">Edit</a>
                        - <a href="?admin&delete=<?php echo $page; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <hr>
            <h2>Create New Page</h2>
            <form action="?admin" method="post">
                <input type="text" name="page" placeholder="Page Name">
                <input type="submit" value="Create">
            </form>
            <hr>
            <?php if (isset($_GET['edit'])): ?>
                <h2>Edit Page: <?php echo ucwords(str_replace('-', ' ', $_GET['edit'])); ?></h2>
                <form action="?page=<?php echo $_GET['edit']; ?>" method="post">
                    <textarea name="content" style="width: 100%; height: 300px;"><?php echo $cms->content; ?></textarea>
                    <br>
                    <input type="submit" value="Save">
                </form>
            <?php endif; ?>
        </body>
        </html>
        <?php
        exit;
    } else {
        // Show login form
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Admin Login</title>
            <link rel="stylesheet" href="https://unpkg.com/sakura.css/css/sakura.css" type="text/css">
        </head>
        <body>
            <h1>Admin Login</h1>
            <form action="?admin" method="post">
                <input type="password" name="password" placeholder="Password">
                <input type="submit" value="Login">
            </form>
        </body>
        </html>
        <?php
        exit;
    }
}

// -----------------------------------------------------------------------------
// Public Website
// -----------------------------------------------------------------------------

$cms = new SimpleCMS();

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $cms->title; ?> - <?php echo SITE_TITLE; ?></title>
    <link rel="stylesheet" href="https://unpkg.com/sakura.css/css/sakura.css" type="text/css">
</head>
<body>
    <header>
        <h1><a href="?"><?php echo SITE_TITLE; ?></a></h1>
        <nav>
            <ul>
                <?php foreach ($cms->getPages() as $page): ?>
                    <li><a href="?page=<?php echo $page; ?>"><?php echo ucwords(str_replace('-', ' ', $page)); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </header>
    <hr>
    <main>
        <h2><?php echo $cms->title; ?></h2>
        <?php echo nl2br($cms->content); ?>
    </main>
    <hr>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_TITLE; ?> | <a href="?admin">Admin</a></p>
    </footer>
</body>
</html>
