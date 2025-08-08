<?php
require_once('dbConnect.php');


$message = '';
$phone = '';
$appointment_date = '';
$mechanic_id = '';
$email = '';
$role = 'user';
$password = '';

// Function to get available mechanics with their available slots
function getAvailableMechanics($conn, $appointment_date = null) {
    $mechanics = [];
    $date_condition = $appointment_date ? "AND date = '$appointment_date'" : "AND date = CURDATE()";
    
    $query = "SELECT m.mecha_id AS id, m.mecha_name AS name, 
             (4 - (SELECT COUNT(*) FROM appointments WHERE mechanic_id = m.mecha_id $date_condition)) as slots
             FROM mechanics m";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $mechanics[] = $row;
        }
    }
    return $mechanics;
}

// Function to check if user already has an appointment on a specific date
function hasExistingAppointment($conn, $phone, $appointment_date) {
    $query = "SELECT COUNT(*) AS total FROM appointments a 
              JOIN users u ON a.user_id = u.id 
              WHERE u.phone = '$phone' AND a.date = '$appointment_date'";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'] > 0;
    }
    return false;
}

// Function to check mechanic availability
function getMechanicAvailability($conn, $mechanic_id, $appointment_date) {
    $query = "SELECT COUNT(*) AS total FROM appointments 
              WHERE mechanic_id = '$mechanic_id' AND date = '$appointment_date'";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return 4 - $row['total']; // Return available slots
    }
    return 0;
}

// Function to find next available mechanic
function findAvailableMechanic($conn, $appointment_date) {
    $available_mechanics = getAvailableMechanics($conn, $appointment_date);
    
    foreach ($available_mechanics as $mechanic) {
        if ($mechanic['slots'] > 0) {
            return $mechanic;
        }
    }
    return null;
}

// Function to create new user
function createUser($conn, $name, $address, $phone, $email = '', $role = 'user', $password = '') {
    $query = "INSERT INTO users (username, address, phone, email, role, password) 
              VALUES ('$name', '$address', '$phone', '$email', '$role', '$password')";
    mysqli_query($conn, $query);
    return mysqli_insert_id($conn);
}

// Function to create new car
function createCar($conn, $license, $car_engine, $car_regi, $user_id) {
    $query = "INSERT INTO cars (car_license, car_engno, car_regi, user_id) 
              VALUES ('$license', '$car_engine', '$car_regi', '$user_id')";
    mysqli_query($conn, $query);
    return mysqli_insert_id($conn);
}

// Function to create appointment
function createAppointment($conn, $mechanic_id, $car_id, $user_id, $appointment_date) {
    $query = "INSERT INTO appointments (mechanic_id, car_id, user_id, date) 
              VALUES ('$mechanic_id', '$car_id', '$user_id', '$appointment_date')";
    return mysqli_query($conn, $query);
}

// Main appointment booking 
function bookAppointment($conn, $form_data) {
    $name = $form_data['your_name'];
    $address = $form_data['address'];
    $phone = $form_data['phone'];
    $license = $form_data['car_license'];
    $car_engine = $form_data['car_engine'];
    $car_regi = $form_data['car_regi'];
    $appointment_date = $form_data['appointment_date'];
    $mechanic_id = $form_data['mechanic_id'];
    
    // Check if user already has an appointment on this date
    if (hasExistingAppointment($conn, $phone, $appointment_date)) {
        return 'You already have an appointment on this date!';
    }
    
    // Check if selected mechanic is available
    $available_slots = getMechanicAvailability($conn, $mechanic_id, $appointment_date);
    
    if ($available_slots <= 0) {
        // Selected mechanic is full, find next available mechanic
        $available_mechanic = findAvailableMechanic($conn, $appointment_date);
        
        if ($available_mechanic) {
            $mechanic_id = $available_mechanic['id'];
            $message = "Your preferred mechanic was full. Appointment booked with {$available_mechanic['name']} instead.";
        } else {
            return 'All mechanics are fully booked for this date!';
        }
    } else {
        $message = 'Appointment Booked Successfully!';
    }
    
    // Create user, car, and appointment
    $user_id = createUser($conn, $name, $address, $phone);
    $car_id = createCar($conn, $license, $car_engine, $car_regi, $user_id);
    
    if (createAppointment($conn, $mechanic_id, $car_id, $user_id, $appointment_date)) {
        return $message;
    } else {
        return 'Error creating appointment: ' . mysqli_error($conn);
    }
}

// Handle request for getting mechanics (js part)
if (isset($_GET['get_mechanics']) && isset($_GET['date'])) {
    header('Content-Type: application/json');
    $mechanics_data = getAvailableMechanics($conn, $_GET['date']);
    echo json_encode($mechanics_data);
    exit;
}

// Initialize mechanics list
$mechanics = getAvailableMechanics($conn);

//form submission
if ($_POST) {
    $message = bookAppointment($conn, $_POST);
    $mechanics = getAvailableMechanics($conn, $_POST['appointment_date']);
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="userpanel.css">
    <title>User panel</title>
</head>

<style>
    .message {
    background-color: #e0ffe0; 
    color: #2d662d;            
    border: 1px solid #a0d6a0;
    padding: 15px 20px;
    margin: 20px auto;
    width: 90%;
    max-width: 600px;
    border-radius: 8px;
    text-align: center;
    font-family: 'Arial', sans-serif;
    font-size: 16px;
}
</style>

<body>
    <header>
        <h1>🔧 Car Workshop</h1>
        <p>Book your appointment with our expert mechanics</p>
    </header>

    <?php if ($message): ?>
        <div class="message">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>


    <main>
        <form method="post">
            <div class="form-group">
                <label>Your Name:</label>
                <input type="text" name="your_name" required>
            </div>

            <div class="form-group">
                <label>Address:</label>
                <textarea name="address" required></textarea>
            </div>

            <div class="form-group">
                <label>Phone:</label>
                <input type="text" name="phone" required>
            </div>
            
            <div class="form-group">
                <label>Car License Number:</label>
                <input type="text" name="car_license" required>
            </div>
            
            <div class="form-group">
                <label>Car Engine Number:</label>
                <input type="text" name="car_engine" required>
            </div>
            
            <div class="form-group">
                <label>Car Registration:</label>
                <input type="text" name="car_regi" required>
            </div>

            <div class="form-group">
                <label>Appointment Date:</label>
                <input type="date" name="appointment_date" required>
            </div>

            <div class="form-group">
                <label>Select Mechanic:</label>
                <select name="mechanic_id" required>
                    <option value="">Choose a mechanic</option>
                    <?php foreach ($mechanics as $mechanic): ?>
                        <option value="<?php echo $mechanic['id']; ?>">
                            <?php echo $mechanic['name'] . ' (' . $mechanic['slots'] . ' free)'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-submit">
                <input type="submit" value="Book Appointment">
            </div>
            
        </form>
    </main>

    <script>
        // Update mechanics when date changes
        document.querySelector('input[name="appointment_date"]').addEventListener('change', function() {
            var date = this.value;
            var select = document.querySelector('select[name="mechanic_id"]');
            
            if (date) {
                fetch('userHome.php?get_mechanics=1&date=' + date)
                    .then(response => response.json())
                    .then(mechanics => {
                        select.innerHTML = '<option value="">Choose a mechanic</option>';
                        mechanics.forEach(mechanic => {
                            select.innerHTML += '<option value="' + mechanic.id + '">' + 
                                               mechanic.name + ' (' + mechanic.slots + ' free)</option>';
                        });
                    });
            }
        });
    </script>


</body>
</html>
