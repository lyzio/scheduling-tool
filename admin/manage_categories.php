<?php
require 'config.php'; // Inkludera databasconfigurationsfilen
include 'header.php'; // Inkludera den gemensamma header-filen

// Hantera formulärinmatning för att lägga till eller uppdatera kategorier
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_category'])) {
        $name = $_POST['name'];
        $color_hex = $_POST['color_hex'];

        // Förberedd fråga för att lägga till ny kategori
        $stmt = $conn->prepare("INSERT INTO categories (name, color_hex) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $color_hex);

        if ($stmt->execute()) {
            echo "Category added successfully.";
        } else {
            echo "Error adding category: " . $conn->error;
        }

        $stmt->close();
    } elseif (isset($_POST['update_category'])) {
        $id = $_POST['category_id'];
        $name = $_POST['name'];
        $color_hex = $_POST['color_hex'];

        // Förberedd fråga för att uppdatera befintlig kategori
        $stmt = $conn->prepare("UPDATE categories SET name = ?, color_hex = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $color_hex, $id);

        if ($stmt->execute()) {
            echo "Category updated successfully.";
        } else {
            echo "Error updating category: " . $conn->error;
        }

        $stmt->close();
    } elseif (isset($_POST['delete_category'])) {
        $id = $_POST['category_id'];

        // Förberedd fråga för att ta bort kategori
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo "Category deleted successfully.";
        } else {
            echo "Error deleting category: " . $conn->error;
        }

        $stmt->close();
    }
}

// Hämta alla kategorier från kategoritabellen för att visa
$categories_result = $conn->query("SELECT * FROM categories");
$categories = [];
while ($category = $categories_result->fetch_assoc()) {
    $categories[] = $category;
}

$conn->close(); // Stäng anslutningen
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories</title>
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <!-- Innehållscontainer -->
    <div class="container">
        <header>
            <h1>Manage Categories</h1>
        </header>

        <!-- Formulär för att lägga till ny kategori -->
        <form method="POST" action="">
            <label for="name">Category Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="color_hex">Color (Hex):</label>
            <input type="text" id="color_hex" name="color_hex" required>

            <button type="submit" name="add_category">Add Category</button>
        </form>

        <!-- Lista över befintliga kategorier -->
        <h2>Existing Categories</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Color</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cat['id']); ?></td>
                        <td><?php echo htmlspecialchars($cat['name']); ?></td>
                        <td style="background-color: <?php echo htmlspecialchars($cat['color_hex']); ?>;"><?php echo htmlspecialchars($cat['color_hex']); ?></td>
                        <td>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($cat['id']); ?>">
                                <input type="text" name="name" value="<?php echo htmlspecialchars($cat['name']); ?>" required>
                                <input type="text" name="color_hex" value="<?php echo htmlspecialchars($cat['color_hex']); ?>" required>
                                <button type="submit" name="update_category">Update</button>
                                <button type="submit" name="delete_category" onclick="return confirm('Are you sure you want to delete this category?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Sidfot -->
    <footer>
        <p>Made by <a href="http://lyzio.net" target="_blank">Oliver</a></p>
    </footer>
</body>
</html>
