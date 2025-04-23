<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function db_connect() {
    try {
        return new PDO("mysql:host=localhost;dbname=reservation_system;charset=utf8", "reservation", "resa", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (PDOException $e) {
        die("Erreur de connexion : " . $e->getMessage());
    }
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$pdo = db_connect();
$user_id = $_SESSION['user_id'];



// Récupérer les infos de l'utilisateur
$stmt = $pdo->prepare("SELECT nom, prenom, email, date_naissance, adresse, telephone FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Mettre à jour les informations
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {

    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Attaque CSRF détectée !");
    }
    
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $date_naissance = $_POST['date_naissance'];
    $adresse = htmlspecialchars($_POST['adresse']);
    $telephone = htmlspecialchars($_POST['telephone']);

    // Vérifier si l'email est déjà pris par un autre utilisateur
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()) {
        $message = "<div class='alert alert-danger'>Cet email est déjà utilisé.</div>";
    } else {
        // Mise à jour des informations
        $stmt = $pdo->prepare("UPDATE users SET nom = ?, prenom = ?, email = ?, date_naissance = ?, adresse = ?, telephone = ? WHERE id = ?");
        $stmt->execute([$nom, $prenom, $email, $date_naissance, $adresse, $telephone, $user_id]);

        $message = "<div class='alert alert-success'>Mise à jour réussie !</div>";

        // Mise à jour de la session
        $_SESSION['nom'] = $nom;
        $_SESSION['prenom'] = $prenom;
        $_SESSION['email'] = $email;
        $_SESSION['date_naissance'] = $date_naissance;
        $_SESSION['adresse'] = $adresse;
        $_SESSION['telephone'] = $telephone;
        header("Location: profil.php");
    }
}

// Suppression du compte
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {

    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Attaque CSRF détectée !");
    }

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);

    session_destroy();
    header("Location: index.php");
    exit();
}

// Déconnexion
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between mb-3">
            <h2>Mon Profil</h2>
            <a href="reservation.php" class="btn btn-secondary">Reservation</a>
        </div>

        <?= isset($message) ? $message : '' ?>
        <div class="card shadow-lg p-4">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div class="mb-3">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($user['nom']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Prénom</label>
                    <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($user['prenom']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Date de naissance</label>
                    <input type="date" name="date_naissance" class="form-control" value="<?= htmlspecialchars($user['date_naissance']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Adresse</label>
                    <input type="text" name="adresse" class="form-control" value="<?= htmlspecialchars($user['adresse']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control" value="<?= htmlspecialchars($user['telephone']) ?>" required>
                </div>
                <button type="submit" name="update" class="btn btn-primary">Mettre à jour</button>
            </form>
        </div>

        <hr>

        <!-- Bouton de déconnexion -->
        <form method="POST" class="mt-3">
            <button type="submit" name="logout" class="btn btn-warning">Se déconnecter</button>
        </form>

        <!-- Bouton de suppression du compte -->
        <form method="POST" class="mt-3">
            <button type="submit" name="delete" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.')">Supprimer mon compte</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
