<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between mb-3">
            <h2>Réserver un créneau</h2>
            <a href="profil.php" class="btn btn-secondary">Mon Profil</a>
        </div>
        <div class="card shadow-lg p-4">
            <?php
            session_start();
            require_once 'config.php';

            if (!isset($_SESSION['user_id'])) {
                echo "<div class='alert alert-danger text-center'>Veuillez vous connecter pour réserver un créneau.</div>";
                exit();
            }

            $pdo = new PDO("mysql:host=localhost;dbname=reservation_system;charset=utf8", "reservation", "resa", [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reserver'])) {
                $date_rdv = $_POST['date_rdv'];
                $heure_rdv = $_POST['heure_rdv'];
                $user_id = $_SESSION['user_id'];
                $date_actuelle = date('Y-m-d');

                if ($date_rdv < $date_actuelle) {
                    echo "<div class='alert alert-danger text-center'>Vous ne pouvez pas réserver un créneau dans le passé.</div>";
                } else {
                    $stmt = $pdo->prepare("SELECT * FROM reservations WHERE date_rdv = ? AND heure_rdv = ?");
                    $stmt->execute([$date_rdv, $heure_rdv]);
                    $existing_reservation = $stmt->fetch();

                    if (!$existing_reservation) {
                        $stmt = $pdo->prepare("INSERT INTO reservations (user_id, date_rdv, heure_rdv) VALUES (?, ?, ?)");
                        $stmt->execute([$user_id, $date_rdv, $heure_rdv]);

                        

                        echo "<div class='alert alert-success text-center'>Réservation confirmée pour le " . date('d/m/Y', strtotime($date_rdv)) . " à $heure_rdv.</div>";
                    } else {
                        echo "<div class='alert alert-danger text-center'>Ce créneau est déjà réservé.</div>";
                    }
                }
            }

            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['annuler'])) {
                $reservation_id = $_POST['reservation_id'];
                $stmt = $pdo->prepare("DELETE FROM reservations WHERE id = ? AND user_id = ?");
                $stmt->execute([$reservation_id, $_SESSION['user_id']]);
                echo "<div class='alert alert-success text-center'>Votre réservation a été annulée.</div>";
            }
            ?>

            <form method="POST" action="" class="mt-3">
                <div class="mb-3">
                    <label class="form-label">Date du rendez-vous</label>
                    <input type="date" name="date_rdv" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Heure du rendez-vous</label>
                    <input type="time" name="heure_rdv" class="form-control" min="08:00" max="20:00" required>
                </div>
                <button type="submit" name="reserver" class="btn btn-primary w-100">Réserver</button>
            </form>
        </div>

        <div class="card shadow-lg p-4 mt-5">
            <h3 class="text-center">Rendez-vous déjà pris</h3>
            <?php
            $stmt = $pdo->query("SELECT reservations.id, users.nom, users.prenom, reservations.date_rdv, reservations.heure_rdv, reservations.user_id FROM reservations JOIN users ON reservations.user_id = users.id ORDER BY reservations.date_rdv, reservations.heure_rdv");
            echo "<ul class='list-group mt-3'>";
            while ($row = $stmt->fetch()) {
                echo "<li class='list-group-item d-flex justify-content-between align-items-center'>
                        <span>{$row['nom']} {$row['prenom']}</span>
                        <span class='badge bg-primary'>" . date('d/m/Y', strtotime($row['date_rdv'])) . " à {$row['heure_rdv']}</span>";
                if ($row['user_id'] == $_SESSION['user_id']) {
                    echo "<form method='POST' action='' class='d-inline'>
                            <input type='hidden' name='reservation_id' value='{$row['id']}'>
                            <button type='submit' name='annuler' class='btn btn-danger btn-sm ms-3'>Annuler</button>
                          </form>";
                }
                echo "</li>";
            }
            echo "</ul>";
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
