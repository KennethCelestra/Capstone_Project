<?php
/**
 * AutoClear CLI Management Tool
 * Run from terminal / PowerShell: php cli.php <command> [options]
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "Forbidden: This script can only be executed via the command line interface.\n";
    exit(1);
}

// Bootstrap application environment
require_once __DIR__ . '/config/app.php';
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/Model.php';
require_once ROOT_PATH . '/app/Models/Admin.php';

// Helper: prompt user for input
function prompt(string $question, bool $hidden = false): string
{
    echo $question;
    $input = trim(fgets(STDIN));
    return $input;
}

// Helper: parse command line options like --email=foo or --name="bar"
function parseOptions(array $argv): array
{
    $options = [];
    foreach ($argv as $arg) {
        if (str_starts_with($arg, '--')) {
            $arg = substr($arg, 2);
            if (strpos($arg, '=') !== false) {
                [$key, $val] = explode('=', $arg, 2);
                $options[$key] = trim($val, " \t\n\r\0\x0B'\"");
            } else {
                $options[$arg] = true;
            }
        }
    }
    return $options;
}

// Show usage help
function showHelp(): void
{
    echo "\n=========================================\n";
    echo "  AutoClear CLI Management Tool\n";
    echo "=========================================\n\n";
    echo "Usage:\n";
    echo "  php cli.php <command> [options]\n\n";
    echo "Available Commands:\n";
    echo "  admin:list                   List all administrator accounts\n";
    echo "  admin:create [options]       Create a new administrator account\n";
    echo "  admin:delete [email|id]      Delete an administrator account\n";
    echo "  admin:password [email|id]    Reset an administrator's password\n";
    echo "  help                         Show this help message\n\n";
    echo "Options for admin:create:\n";
    echo "  --name=\"Full Name\"           Admin full name\n";
    echo "  --email=\"admin@domain.com\"   Admin email address\n";
    echo "  --password=\"secret\"          Admin password\n\n";
    echo "Examples:\n";
    echo "  php cli.php admin:list\n";
    echo "  php cli.php admin:create\n";
    echo "  php cli.php admin:create --name=\"John Doe\" --email=\"john@school.edu\" --password=\"secret123\"\n";
    echo "  php cli.php admin:delete admin2@school.edu\n";
    echo "  php cli.php admin:password admin@school.edu\n\n";
}

// Main execution
$command = $argv[1] ?? 'help';
$options = parseOptions(array_slice($argv, 2));

$adminModel = new Admin();

switch ($command) {

    // ----------------------------------------------------
    // Command: admin:list
    // ----------------------------------------------------
    case 'admin:list':
        $admins = $adminModel->findAll();
        if (empty($admins)) {
            echo "\nNo administrators found in the database.\n\n";
            exit(0);
        }

        echo "\n========================================================================================\n";
        echo "  Registered Administrators (" . count($admins) . " total)\n";
        echo "========================================================================================\n";
        printf("%-6s | %-28s | %-32s | %-19s\n", "ID", "Full Name", "Email", "Created At");
        echo str_repeat('-', 90) . "\n";

        foreach ($admins as $adm) {
            printf(
                "%-6d | %-28s | %-32s | %-19s\n",
                $adm['id'],
                mb_strimwidth($adm['full_name'], 0, 28, '...'),
                mb_strimwidth($adm['email'], 0, 32, '...'),
                $adm['created_at'] ?? 'N/A'
            );
        }
        echo "\n";
        break;

    // ----------------------------------------------------
    // Command: admin:create
    // ----------------------------------------------------
    case 'admin:create':
        echo "\n=== Create New Administrator ===\n";

        // Full Name
        $name = $options['name'] ?? null;
        while (empty($name)) {
            $name = prompt("Enter Full Name: ");
            if (empty($name)) {
                echo "[!] Full Name cannot be empty.\n";
            }
        }

        // Email
        $email = $options['email'] ?? null;
        while (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if ($email !== null) {
                echo "[!] Please enter a valid email address.\n";
            }
            $email = prompt("Enter Email: ");
        }

        // Check if email already exists
        $existing = $adminModel->findByEmail($email);
        if ($existing) {
            echo "\n[ERROR] An administrator with email '{$email}' already exists (ID: {$existing['id']}).\n\n";
            exit(1);
        }

        // Password
        $password = $options['password'] ?? null;
        while (empty($password) || strlen($password) < 6) {
            if ($password !== null) {
                echo "[!] Password must be at least 6 characters long.\n";
            }
            $password = prompt("Enter Password (min 6 characters): ");
        }

        // Hash and save
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $newId = $adminModel->create($name, $email, $hash);

        if ($newId) {
            echo "\n[SUCCESS] Administrator created successfully!\n";
            echo "  ID:        {$newId}\n";
            echo "  Name:      {$name}\n";
            echo "  Email:     {$email}\n\n";
        } else {
            echo "\n[ERROR] Failed to insert administrator into the database.\n\n";
            exit(1);
        }
        break;

    // ----------------------------------------------------
    // Command: admin:delete
    // ----------------------------------------------------
    case 'admin:delete':
        echo "\n=== Delete Administrator ===\n";

        $target = $argv[2] ?? ($options['email'] ?? ($options['id'] ?? null));

        if (empty($target)) {
            $target = prompt("Enter Admin Email or ID to delete: ");
        }

        if (empty($target)) {
            echo "[!] No identifier provided. Aborting.\n\n";
            exit(1);
        }

        // Find by ID or Email
        $admin = is_numeric($target)
            ? $adminModel->findById((int) $target)
            : $adminModel->findByEmail($target);

        if (!$admin) {
            echo "\n[ERROR] No administrator found matching '{$target}'.\n\n";
            exit(1);
        }

        // Safeguard: Ensure at least one admin remains
        $totalAdmins = $adminModel->count();
        if ($totalAdmins <= 1) {
            echo "\n[ABORTED] Cannot delete '{$admin['email']}'. This is the only remaining administrator account in the system.\n\n";
            exit(1);
        }

        // Confirmation prompt
        echo "\nAdmin details to delete:\n";
        echo "  ID:    {$admin['id']}\n";
        echo "  Name:  {$admin['full_name']}\n";
        echo "  Email: {$admin['email']}\n\n";

        $confirm = strtolower(prompt("Are you sure you want to permanently delete this administrator? (y/N): "));
        if ($confirm !== 'y' && $confirm !== 'yes') {
            echo "\nDeletion cancelled.\n\n";
            exit(0);
        }

        if ($adminModel->delete((int) $admin['id'])) {
            echo "\n[SUCCESS] Administrator '{$admin['email']}' (ID: {$admin['id']}) has been deleted.\n\n";
        } else {
            echo "\n[ERROR] Failed to delete administrator from the database.\n\n";
            exit(1);
        }
        break;

    // ----------------------------------------------------
    // Command: admin:password
    // ----------------------------------------------------
    case 'admin:password':
        echo "\n=== Reset Administrator Password ===\n";

        $target = $argv[2] ?? ($options['email'] ?? ($options['id'] ?? null));
        if (empty($target)) {
            $target = prompt("Enter Admin Email or ID: ");
        }

        $admin = is_numeric($target)
            ? $adminModel->findById((int) $target)
            : $adminModel->findByEmail($target);

        if (!$admin) {
            echo "\n[ERROR] No administrator found matching '{$target}'.\n\n";
            exit(1);
        }

        echo "Resetting password for: {$admin['full_name']} ({$admin['email']})\n";

        $newPassword = '';
        while (empty($newPassword) || strlen($newPassword) < 6) {
            if ($newPassword !== '') {
                echo "[!] Password must be at least 6 characters long.\n";
            }
            $newPassword = prompt("Enter New Password: ");
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        if ($adminModel->updatePassword((int) $admin['id'], $hash)) {
            echo "\n[SUCCESS] Password updated successfully for {$admin['email']}.\n\n";
        } else {
            echo "\n[ERROR] Failed to update password.\n\n";
            exit(1);
        }
        break;

    // ----------------------------------------------------
    // Help / Unknown command
    // ----------------------------------------------------
    case 'help':
    case '--help':
    case '-h':
        showHelp();
        break;

    default:
        echo "\n[ERROR] Unknown command '{$command}'.\n";
        showHelp();
        exit(1);
}
