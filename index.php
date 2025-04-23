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
            <h2>Système de Réservation</h2>
        </div>
        
        <!-- Formulaire d'inscription -->
        <div class="card shadow-lg p-4">
            <form method="POST" action="">
                <h3>Inscription</h3>
                <div class="mb-3">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Prénom</label>
                    <input type="text" name="prenom" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Date de naissance</label>
                    <input type="date" name="date_naissance" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Adresse</label>
                    <input type="text" name="adresse" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">N° de téléphone</label>
                    <input type="text" name="telephone" class="form-control" required>
                </div>
                <button type="submit" name="register" href="reservation.php" class="btn btn-primary">S'inscrire</button>
            </form>
        </div>
        
        <hr>

        <!-- Formulaire de connexion -->
        <div class="card shadow-lg p-4">
            <form method="POST" action="">
                <h3>Connexion</h3>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" name="login" href="reservation.php" class="btn btn-success">Se connecter</button>
            </form>
        </div>
    </div>

    <?php
    session_start();
    require_once 'config.php';

    // Connexion à la base de données
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

    // Inscription d'un utilisateur
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
        $pdo = db_connect();
        $nom = htmlspecialchars($_POST['nom']);
        $prenom = htmlspecialchars($_POST['prenom']);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $date_naissance = $_POST['date_naissance'];
        $adresse = htmlspecialchars($_POST['adresse']);
        $telephone = htmlspecialchars($_POST['telephone']);
    
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            echo "<div class='alert alert-danger'>Email déjà utilisé.</div>";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, password, date_naissance, adresse, telephone) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $email, $password, $date_naissance, $adresse, $telephone]);
    
            // Connexion automatique après inscription
            $user_id = $pdo->lastInsertId();
            $_SESSION['user_id'] = $user_id;
    
            header("Location: profil.php");
            exit();
        }
    }
    

    // Connexion d'un utilisateur
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
        $pdo = db_connect();
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
    
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
    
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: profil.php");
            exit();
        } else {
            echo "<div class='alert alert-danger'>Identifiants incorrects.</div>";
        }
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
