<?php
//include('../utils/auth.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//include('./utils/config.php');
include '../dbs.project/config.php';
$search = '';
$result = null;

if (isset($_GET['search'])){
    $search = trim($_GET['search']);
    if ($search !=''){
        $stmt = $conn->prepare("
            SELECT user_id, name, email, user_type, address, phone, created_at
            FROM users
            WHERE name LIKE ? OR email LIKE ?
            ORDER BY created_at DESC
        ");
        $like = "%$search%";
        $stmt->bind_param("ss", $like, $like);
        $stmt->execute();
        $result = $stmt->get_result();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WNK Member Lookup Page</title>
    <style>
        body{
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #e3d09d;
        }
        h1{color: #4a3327;}
        input[type="text"] {
            padding: 5px;
            width: 350px;
        }
        input[type="submit"]{
            padding: 10px 20px;
            background-color: green;
            color: white;
        }
        table{
            border-collapse: collapse;
            width: 100%;
        }
        th, td{
            border: 1px solid #111111;
            padding: 8px;
            text-align: left;
        }
        .no-results{
            color: #7d0101;
            margin: 30px;
        }
    </style>
</head>

<body>
    <h1>Member Lookup</h1>
    <form method="GET" action="">
        <input type="text" name="search" placeholder="Enter name or email" value="<?php echo htmlspecialchars($search)?>" required>
        <input type="submit" value="Search">
    </form>
    
    <?php if ($search!==""):?>
        <h3>Results:</h3>
        <?php if ($result && $result->num_rows>0):?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>User Type</th>
                    <th>Address</th>
                    <th>Phone Number</th>
                    <th>Joined</th>
                </tr>
                <?php while($row=$result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['user_id'])?></td>
                    <td><?php echo htmlspecialchars($row['name'])?></td>
                    <td><?php echo htmlspecialchars($row['email'])?></td>
                    <td><?php echo htmlspecialchars($row['user_type'])?></td>
                    <td><?php echo htmlspecialchars($row['address'])?></td>
                    <td><?php echo htmlspecialchars($row['phone'])?></td>
                    <td><?php echo htmlspecialchars($row['created_at'])?></td>
                </tr>
                <?php endwhile;?>
            </table>
        <?php else:?>
            <p class="no-results"> No members found matching your search.</p>
        <?php endif;?>
    <?php endif;?>
</body>
</html>