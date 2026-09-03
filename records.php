<?php
require_once "includes/config.php";
require_once "includes/auth.php";

$userId = $_SESSION['user_id'];
$editRecord = null;

// ---------- Handle form submission (add or update) ----------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cropName = trim($_POST['crop_name']);
    $plantingDate = $_POST['planting_date'] !== "" ? $_POST['planting_date'] : null;
    $harvestDate = $_POST['expected_harvest_date'] !== "" ? $_POST['expected_harvest_date'] : null;
    $inputCost = $_POST['input_cost'] !== "" ? $_POST['input_cost'] : 0;
    $labourCost = $_POST['labour_cost'] !== "" ? $_POST['labour_cost'] : 0;
    $otherCost = $_POST['other_cost'] !== "" ? $_POST['other_cost'] : 0;
    $quantity = $_POST['quantity_harvested'] !== "" ? $_POST['quantity_harvested'] : 0;
    $unit = $_POST['unit'];
    $status = $_POST['status'];

    // Handle an uploaded crop photo, if one was provided
    $photoPath = !empty($_POST['existing_photo']) ? $_POST['existing_photo'] : null;
    if (isset($_FILES['crop_photo']) && $_FILES['crop_photo']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileType = mime_content_type($_FILES['crop_photo']['tmp_name']);
        if (in_array($fileType, $allowedTypes) && $_FILES['crop_photo']['size'] <= 2 * 1024 * 1024) {
            $extension = pathinfo($_FILES['crop_photo']['name'], PATHINFO_EXTENSION);
            $newFileName = 'crop_' . uniqid() . '.' . $extension;
            $destination = 'uploads/' . $newFileName;
            if (move_uploaded_file($_FILES['crop_photo']['tmp_name'], $destination)) {
                $photoPath = $destination;
            }
        }
    }

    if (!empty($_POST['record_id'])) {
        // Updating an existing record — only allow if it belongs to this user
        $recordId = (int) $_POST['record_id'];
        $stmt = $conn->prepare("UPDATE farm_records SET crop_name=?, planting_date=?, expected_harvest_date=?, input_cost=?, labour_cost=?, other_cost=?, quantity_harvested=?, unit=?, photo_path=?, status=? WHERE id=? AND user_id=?");
        $stmt->bind_param("sssddddsssii", $cropName, $plantingDate, $harvestDate, $inputCost, $labourCost, $otherCost, $quantity, $unit, $photoPath, $status, $recordId, $userId);
        $stmt->execute();
    } else {
        // Adding a new record
        $stmt = $conn->prepare("INSERT INTO farm_records (user_id, crop_name, planting_date, expected_harvest_date, input_cost, labour_cost, other_cost, quantity_harvested, unit, photo_path, status) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("isssddddsss", $userId, $cropName, $plantingDate, $harvestDate, $inputCost, $labourCost, $otherCost, $quantity, $unit, $photoPath, $status);
        $stmt->execute();
    }

    // Redirect after saving so refreshing the page doesn't resubmit the form
    header("Location: records.php");
    exit();
}

// ---------- Load a record into the form if editing ----------
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM farm_records WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $editId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $editRecord = $result->fetch_assoc();
    }
}

// ---------- Fetch all records for this user ----------
$stmt = $conn->prepare("SELECT * FROM farm_records WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$records = $stmt->get_result();

$pageTitle = "Farm Records";
include "includes/header.php";
?>

<div class="page-heading">
    <h1><?= t('farm_records') ?></h1>
    <p><?= t('farm_records_intro') ?></p>
</div>

<div class="card">
    <div class="card-head">
        <h2 style="font-size:1.1rem;"><?= $editRecord ? t('edit_record') : t('add_new_record') ?></h2>
        <?php if ($editRecord): ?>
            <a href="records.php" class="btn-outline">Cancel edit</a>
        <?php endif; ?>
    </div>

    <form method="POST" action="records.php" enctype="multipart/form-data">
        <?php if ($editRecord): ?>
            <input type="hidden" name="record_id" value="<?= $editRecord['id'] ?>">
            <input type="hidden" name="existing_photo" value="<?= htmlspecialchars($editRecord['photo_path'] ?? '') ?>">
        <?php endif; ?>

        <div class="form-grid">
            <div class="field full">
                <label for="crop_name">Crop name</label>
                <input type="text" id="crop_name" name="crop_name" required
                       value="<?= $editRecord ? htmlspecialchars($editRecord['crop_name']) : '' ?>"
                       placeholder="e.g. Tomatoes">
            </div>

            <div class="field">
                <label for="planting_date">Planting date</label>
                <input type="date" id="planting_date" name="planting_date"
                       value="<?= $editRecord['planting_date'] ?? '' ?>">
            </div>
            <div class="field">
                <label for="expected_harvest_date">Expected harvest date</label>
                <input type="date" id="expected_harvest_date" name="expected_harvest_date"
                       value="<?= $editRecord['expected_harvest_date'] ?? '' ?>">
            </div>

            <div class="field">
                <label for="input_cost">Input cost (KES)</label>
                <input type="number" step="0.01" id="input_cost" name="input_cost"
                       value="<?= $editRecord['input_cost'] ?? '0' ?>">
            </div>
            <div class="field">
                <label for="labour_cost">Labour cost (KES)</label>
                <input type="number" step="0.01" id="labour_cost" name="labour_cost"
                       value="<?= $editRecord['labour_cost'] ?? '0' ?>">
            </div>
            <div class="field">
                <label for="other_cost">Other cost (KES)</label>
                <input type="number" step="0.01" id="other_cost" name="other_cost"
                       value="<?= $editRecord['other_cost'] ?? '0' ?>">
            </div>
            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="planted" <?= (($editRecord['status'] ?? '') === 'planted') ? 'selected' : '' ?>>Planted</option>
                    <option value="growing" <?= (($editRecord['status'] ?? '') === 'growing') ? 'selected' : '' ?>>Growing</option>
                    <option value="harvested" <?= (($editRecord['status'] ?? '') === 'harvested') ? 'selected' : '' ?>>Harvested</option>
                </select>
            </div>

            <div class="field">
                <label for="quantity_harvested">Quantity harvested</label>
                <input type="number" step="0.01" id="quantity_harvested" name="quantity_harvested"
                       value="<?= $editRecord['quantity_harvested'] ?? '0' ?>">
            </div>
            <div class="field">
                <label for="unit">Unit</label>
                <select id="unit" name="unit">
                    <?php foreach (['kg', 'bags', 'crates', 'litres', 'tonnes'] as $u): ?>
                        <option value="<?= $u ?>" <?= (($editRecord['unit'] ?? 'kg') === $u) ? 'selected' : '' ?>><?= $u ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field full">
                <label for="crop_photo">Crop photo (optional, JPG/PNG/WEBP, max 2MB)</label>
                <input type="file" id="crop_photo" name="crop_photo" accept="image/jpeg,image/png,image/webp">
                <?php if ($editRecord && !empty($editRecord['photo_path'])): ?>
                    <div style="margin-top:8px;">
                        <img src="<?= htmlspecialchars($editRecord['photo_path']) ?>" class="crop-thumb" alt="Current photo">
                        <span style="font-size:0.82rem; color:#7a6f5c;"> Current photo — upload a new one to replace it.</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <br>
        <button type="submit" class="btn-small"><?= $editRecord ? t('save_changes') : t('add_record') ?></button>
    </form>
</div>

<div class="card">
    <div class="card-head">
        <h2 style="font-size:1.1rem;"><?= t('all_records') ?></h2>
    </div>

    <?php if ($records->num_rows === 0): ?>
        <div class="empty-state">No farm records yet. Add your first crop above.</div>
    <?php else: ?>
        <table>
            <tr>
                <th>Photo</th>
                <th>Crop</th>
                <th>Status</th>
                <th>Total cost</th>
                <th>Harvested</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $records->fetch_assoc()):
                $totalCost = $row['input_cost'] + $row['labour_cost'] + $row['other_cost'];
            ?>
                <tr>
                    <td>
                        <?php if (!empty($row['photo_path'])): ?>
                            <img src="<?= htmlspecialchars($row['photo_path']) ?>" class="crop-thumb" alt="<?= htmlspecialchars($row['crop_name']) ?>">
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['crop_name']) ?></td>
                    <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                    <td>KES <?= number_format($totalCost, 2) ?></td>
                    <td><?= $row['quantity_harvested'] > 0 ? number_format($row['quantity_harvested'], 1) . ' ' . htmlspecialchars($row['unit']) : '—' ?></td>
                    <td class="action-links">
                        <a href="records.php?edit=<?= $row['id'] ?>">Edit</a>
                        <a href="delete_record.php?id=<?= $row['id'] ?>" class="delete-link"
                           onclick="return confirm('Delete this record? This cannot be undone.');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>
