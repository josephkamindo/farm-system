<?php
require_once "includes/config.php";
require_once "includes/auth.php";

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];
$editListing = null;

// ---------- Handle form submission (add or update a listing) ----------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cropName = trim($_POST['crop_name']);
    $quantity = $_POST['quantity_available'] !== "" ? $_POST['quantity_available'] : 0;
    $unit = $_POST['unit'];
    $price = $_POST['asking_price'] !== "" ? $_POST['asking_price'] : 0;
    $location = trim($_POST['location']);
    $status = $_POST['status'];

    if (!empty($_POST['listing_id'])) {
        $listingId = (int) $_POST['listing_id'];
        $stmt = $conn->prepare("UPDATE market_listings SET crop_name=?, quantity_available=?, unit=?, asking_price=?, location=?, status=? WHERE id=? AND user_id=?");
        $stmt->bind_param("sdsdssii", $cropName, $quantity, $unit, $price, $location, $status, $listingId, $userId);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO market_listings (user_id, crop_name, quantity_available, unit, asking_price, location, status) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("isdsdss", $userId, $cropName, $quantity, $unit, $price, $location, $status);
        $stmt->execute();
    }

    header("Location: market.php");
    exit();
}

// ---------- Load a listing into the form if editing ----------
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM market_listings WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $editId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $editListing = $result->fetch_assoc();
    }
}

// ---------- My listings (only this user's) ----------
$stmt = $conn->prepare("SELECT * FROM market_listings WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$myListings = $stmt->get_result();

// ---------- All available listings from every farmer (the marketplace) ----------
$marketplace = $conn->query("
    SELECT market_listings.*, users.full_name, users.location AS farmer_location
    FROM market_listings
    JOIN users ON market_listings.user_id = users.id
    WHERE market_listings.status = 'available'
    ORDER BY market_listings.created_at DESC
");

$pageTitle = "Market Linkage";
include "includes/header.php";
?>

<div class="page-heading">
    <h1><?= t('market_linkage') ?></h1>
    <p><?= t('market_intro') ?></p>
</div>

<?php if ($role === 'farmer'): ?>
<div class="card">
    <div class="card-head">
        <h2 style="font-size:1.1rem;"><?= $editListing ? t('edit_record') : t('list_your_produce') ?></h2>
        <?php if ($editListing): ?>
            <a href="market.php" class="btn-outline">Cancel edit</a>
        <?php endif; ?>
    </div>

    <form method="POST" action="market.php">
        <?php if ($editListing): ?>
            <input type="hidden" name="listing_id" value="<?= $editListing['id'] ?>">
        <?php endif; ?>

        <div class="form-grid">
            <div class="field full">
                <label for="crop_name">Crop / produce name</label>
                <input type="text" id="crop_name" name="crop_name" required
                       value="<?= $editListing ? htmlspecialchars($editListing['crop_name']) : '' ?>"
                       placeholder="e.g. Tomatoes">
            </div>

            <div class="field">
                <label for="quantity_available">Quantity available</label>
                <input type="number" step="0.01" id="quantity_available" name="quantity_available"
                       value="<?= $editListing['quantity_available'] ?? '0' ?>" required>
            </div>
            <div class="field">
                <label for="unit">Unit</label>
                <select id="unit" name="unit">
                    <?php foreach (['kg', 'bags', 'crates', 'litres', 'tonnes'] as $u): ?>
                        <option value="<?= $u ?>" <?= (($editListing['unit'] ?? 'kg') === $u) ? 'selected' : '' ?>><?= $u ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="asking_price">Asking price per unit (KES)</label>
                <input type="number" step="0.01" id="asking_price" name="asking_price"
                       value="<?= $editListing['asking_price'] ?? '0' ?>" required>
            </div>
            <div class="field">
                <label for="location">Location</label>
                <input type="text" id="location" name="location" placeholder="e.g. Kiambu Town"
                       value="<?= $editListing ? htmlspecialchars($editListing['location']) : '' ?>">
            </div>

            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="available" <?= (($editListing['status'] ?? 'available') === 'available') ? 'selected' : '' ?>>Available</option>
                    <option value="sold" <?= (($editListing['status'] ?? '') === 'sold') ? 'selected' : '' ?>>Sold</option>
                </select>
            </div>
        </div>

        <br>
        <button type="submit" class="btn-small"><?= $editListing ? t('save_changes') : t('list_your_produce') ?></button>
    </form>
</div>

<div class="card">
    <div class="card-head">
        <h2 style="font-size:1.1rem;"><?= t('my_listings') ?></h2>
    </div>

    <?php if ($myListings->num_rows === 0): ?>
        <div class="empty-state">You haven't listed any produce yet.</div>
    <?php else: ?>
        <table>
            <tr>
                <th>Crop</th>
                <th>Quantity</th>
                <th>Price / unit</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $myListings->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['crop_name']) ?></td>
                    <td><?= number_format($row['quantity_available'], 1) ?> <?= htmlspecialchars($row['unit']) ?></td>
                    <td>KES <?= number_format($row['asking_price'], 2) ?></td>
                    <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                    <td class="action-links">
                        <a href="market.php?edit=<?= $row['id'] ?>">Edit</a>
                        <a href="delete_listing.php?id=<?= $row['id'] ?>" class="delete-link"
                           onclick="return confirm('Delete this listing?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-head">
        <h2 style="font-size:1.1rem;"><?= t('available_marketplace') ?></h2>
    </div>

    <?php if ($marketplace->num_rows === 0): ?>
        <div class="empty-state">No produce listed yet. Check back soon.</div>
    <?php else: ?>
        <table>
            <tr>
                <th>Crop</th>
                <th>Quantity</th>
                <th>Price / unit</th>
                <th>Farmer</th>
                <th>Location</th>
            </tr>
            <?php while ($row = $marketplace->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['crop_name']) ?></td>
                    <td><?= number_format($row['quantity_available'], 1) ?> <?= htmlspecialchars($row['unit']) ?></td>
                    <td>KES <?= number_format($row['asking_price'], 2) ?></td>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['location'] ?: $row['farmer_location']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>
