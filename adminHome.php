<?php
require_once('dbConnect.php');

function getMechanicSlots($conn, $date) {
    $slots = [];
    $sql = "SELECT mechanic_id, COUNT(*) as count FROM appointments WHERE date = '" . mysqli_real_escape_string($conn, $date) . "' GROUP BY mechanic_id";
    $res = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($res)) {
        $slots[$row['mechanic_id']] = $row['count'];
    }
    return $slots;
}

// Handle update if form is submitted
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apt_id'])) {
    $apt_id = intval($_POST['apt_id']);
    $new_date = $_POST['date'];
    $new_mechanic = intval($_POST['mechanic_id']);
    $slots = getMechanicSlots($conn, $new_date);
    if (isset($slots[$new_mechanic]) && $slots[$new_mechanic] >= 4) {
        $message = "Error: Selected mechanic is already fully booked on $new_date.";
    } else {
        $update_sql = "UPDATE appointments SET date = '$new_date', mechanic_id = $new_mechanic WHERE apt_id = $apt_id";
        mysqli_query($conn, $update_sql);
    }
}

// all appointments with required info
$sql = "SELECT a.apt_id, u.username, u.phone, c.car_regi, a.date, m.mecha_id, m.mecha_name
        FROM appointments a
        JOIN users u ON a.user_id = u.id
        JOIN cars c ON a.car_id = c.id
        JOIN mechanics m ON a.mechanic_id = m.mecha_id
        ORDER BY a.date DESC";
$result = mysqli_query($conn, $sql);

// all mechanics for dropdown
$mech_sql = "SELECT mecha_id, mecha_name FROM mechanics";
$mech_result = mysqli_query($conn, $mech_sql);
$mechanics = [];
while ($row = mysqli_fetch_assoc($mech_result)) {
    $mechanics[$row['mecha_id']] = $row['mecha_name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Appointments</title>
    <style>
        * { background: rgb(212, 231, 175);}
        h2 { text-align: center; }
        table { border-collapse: collapse; width: 100%; border-color: #223301; }
        th, td { border: 2px solid #3d5806ff; padding: 8px; text-align: left; }
        th { background: #29be74ff; }
        form { margin: 0; }
        .edit-btn { background: #007bff; color: #fff; border: none; padding: 4px 10px; cursor: pointer; border-radius: 3px; }
        .edit-btn:hover { background: #0056b3; }
        .error { color: red; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2>Admin Panel - Appointments</h2>
    <?php if ($message): ?><div class="error"><?php echo $message; ?></div><?php endif; ?>
    <table>
        <thead>
            <tr>
                <th>Client Name</th>
                <th>Phone</th>
                <th>Car Registration</th>
                <th>Appointment Date</th>
                <th>Mechanic</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)):
            $slots = getMechanicSlots($conn, $row['date']); ?>
            <tr>
                <form method="post">
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['car_regi']); ?></td>
                    <td>
                        <input type="date" name="date" value="<?php echo htmlspecialchars($row['date']); ?>" required>
                    </td>
                    <td>
                        <select name="mechanic_id" required>
                            <?php foreach ($mechanics as $id => $name):
                                $count = isset($slots[$id]) ? $slots[$id] : 0;
                                $full = $count >= 4 && $id != $row['mecha_id']; ?>
                                <option value="<?php echo $id; ?>" <?php if ($id == $row['mecha_id']) echo 'selected'; if ($full) echo ' disabled'; ?>>
                                    <?php echo htmlspecialchars($name) . ' (' . (4 - $count) . ' free)'; if ($full) echo ' (full)'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <input type="hidden" name="apt_id" value="<?php echo $row['apt_id']; ?>">
                        <button type="submit" class="edit-btn">Update</button>
                    </td>
                </form>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
