<?php
session_start();
include "../config/db.php";

/* LOGIN CHECK */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* TRIP ID CHECK */
if (!isset($_GET['trip_id'])) {
    header("Location: list.php");
    exit;
}

$trip_id = $_GET['trip_id'];

/* FETCH TRIP */
$tripQuery = mysqli_query($conn,
    "SELECT title, destination, created_by
     FROM trips WHERE id = $trip_id"
);

if (mysqli_num_rows($tripQuery) == 0) {
    die("Trip not found");
}

$trip = mysqli_fetch_assoc($tripQuery);

/* CREATOR CHECK */
if ($trip['created_by'] != $user_id) {
    die("Unauthorized access");
}

/* DELETE RELATED DATA (ORDER MATTERS) */
mysqli_query($conn, "DELETE FROM settlements WHERE trip_id = $trip_id");
mysqli_query($conn, "DELETE FROM expenses WHERE trip_id = $trip_id");
mysqli_query($conn, "DELETE FROM notes WHERE trip_id = $trip_id");
mysqli_query($conn, "DELETE FROM itinerary WHERE trip_id = $trip_id");
mysqli_query($conn, "DELETE FROM trip_members WHERE trip_id = $trip_id");
mysqli_query($conn, "DELETE FROM trips WHERE id = $trip_id");

/* OPTIONAL: ACTIVITY LOG (if you ever re-enable logs)
$tripLabel = $trip['title'] . " (" . $trip['destination'] . ")";
mysqli_query($conn,
  "INSERT INTO activity_logs (user_id, action)
   VALUES ($user_id, 'Deleted trip: $tripLabel')"
);
*/

header("Location: list.php");
exit;
