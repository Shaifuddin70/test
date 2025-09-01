<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

// --- Handle ADD action ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_advertisement'])) {
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($category_id) || empty($title)) {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Category and Title are required.'];
    } else {
        $image_name = handleImageUpload($_FILES['image']);
        if ($image_name !== false) {
            $stmt = $pdo->prepare(
                "INSERT INTO advertisements (category_id, title, description, image, is_active) VALUES (:category_id, :title, :description, :image, :is_active)"
            );
            $params = [
                ':category_id' => $category_id,
                ':title' => $title,
                ':description' => $description,
                ':image' => $image_name,
                ':is_active' => $is_active
            ];
            if ($stmt->execute($params)) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Advertisement added successfully!'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Failed to add advertisement.'];
            }
        }
    }
    redirect('advertisements.php');
}

// --- Handle DELETE action ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_advertisement'])) {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($id) {
        // First, get the image filename to delete the file
        $stmt = $pdo->prepare("SELECT image FROM advertisements WHERE id = ?");
        $stmt->execute([$id]);
        $image_to_delete = $stmt->fetchColumn();

        // Then, delete the database record
        $delete_stmt = $pdo->prepare("DELETE FROM advertisements WHERE id = ?");
        if ($delete_stmt->execute([$id])) {
            // After successful DB deletion, delete the image file
            if ($image_to_delete) {
                $file_path = 'assets/uploads/' . $image_to_delete;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Advertisement deleted successfully!'];
        } else {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Failed to delete advertisement.'];
        }
    }
    redirect('advertisements.php');
}


// Fetch categories for the dropdown
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
// Fetch existing advertisements
$advertisements = $pdo->query("SELECT a.*, c.name as category_name FROM advertisements a JOIN categories c ON a.category_id = c.id ORDER BY a.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-card mb-4">
    <div class="card-header-modern">
        <h3 class="card-title-modern"><i class="bi bi-megaphone-fill me-2"></i>Add New Advertisement</h3>
    </div>
    <div class="p-4">
        <form method="post" enctype="multipart/form-data">
            <div class="row g-4">
                <div class="col-md-4">
                    <label class="form-label form-label-modern">Category *</label>
                    <select name="category_id" class="form-select form-control-modern" required>
                        <option value="" disabled selected>-- Select Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label form-label-modern">Title *</label>
                    <input type="text" name="title" class="form-control form-control-modern" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label form-label-modern">Short Description</label>
                    <input type="text" name="description" class="form-control form-control-modern">
                </div>
                <div class="col-md-6">
                    <label class="form-label form-label-modern">Image *</label>
                    <input type="file" name="image" class="form-control form-control-modern" required accept="image/*">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <label class="toggle-switch me-3">
                        <input type="checkbox" name="is_active" checked>
                        <span class="slider"></span>
                    </label>
                    <span>Active</span>
                </div>
                <div class="col-md-3 d-flex align-items-end justify-content-end">
                    <button type="submit" class="btn btn-primary-modern btn-modern" name="add_advertisement">
                        <i class="bi bi-plus-circle me-2"></i>Add Advertisement
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="main-card">
    <div class="card-header-modern">
        <h3 class="card-title-modern"><i class="bi bi-list-ul me-2"></i>Current Advertisements</h3>
    </div>
    <div class="p-0">
        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($advertisements)): ?>
                        <tr>
                            <td colspan="6" class="empty-state text-center p-5">
                                <i class="bi bi-megaphone" style="font-size: 3rem; opacity: 0.3;"></i>
                                <h4 class="mt-3">No advertisements found</h4>
                                <p class="text-muted">Create your first advertisement using the form above.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($advertisements as $ad): ?>
                            <tr>
                                <td><?= esc_html($ad['id']) ?></td>
                                <td>
                                    <img src="assets/uploads/<?= esc_html($ad['image']) ?>" alt="<?= esc_html($ad['title']) ?>" class="product-image">
                                </td>
                                <td><?= esc_html($ad['title']) ?></td>
                                <td><?= esc_html($ad['category_name']) ?></td>
                                <td>
                                    <span class="badge badge-modern <?= $ad['is_active'] ? 'badge-status' : 'badge-deleted' ?>">
                                        <?= $ad['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="post" onsubmit="return confirm('Are you sure you want to delete this advertisement?');">
                                        <input type="hidden" name="id" value="<?= $ad['id'] ?>">
                                        <button type="submit" name="delete_advertisement" class="btn btn-action btn-delete">
                                            <i class="bi bi-trash me-1"></i>Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>