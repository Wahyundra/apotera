<?php
include_once '../config.php';
include_once '../functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $name = sanitize_input($_POST['name']);
            $description = sanitize_input($_POST['description']);
            $price = floatval($_POST['price']);
            $category = sanitize_input($_POST['category']);
            $stock = intval($_POST['stock']);

            // Handle image upload
            $image_url = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['image']['name'];
                $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                $filesize = $_FILES['image']['size'];

                // Validate file type
                if (in_array(strtolower($filetype), $allowed)) {
                    // Verify file is actually an image
                    if (getimagesize($_FILES['image']['tmp_name'])) {
                        // Limit file size to 2MB
                        if ($filesize <= 2000000) {
                            // Create unique filename
                            $new_filename = uniqid() . '.' . $filetype;
                            $upload_dir = '../uploads/products/';

                            // Create directory if it doesn't exist
                            if (!is_dir($upload_dir)) {
                                mkdir($upload_dir, 0777, true);
                            }

                            $target = $upload_dir . $new_filename;

                            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                                $image_url = 'uploads/products/' . $new_filename;
                            } else {
                                $message = "Gagal mengunggah gambar.";
                            }
                        } else {
                            $message = "Ukuran file terlalu besar. Maksimal 2MB.";
                        }
                    } else {
                        $message = "File bukan gambar yang valid.";
                    }
                } else {
                    $message = "Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.";
                }
            }

            if (empty($message)) {
                $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category, stock, image_url) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $description, $price, $category, $stock, $image_url]);
                $message = "Produk berhasil ditambahkan.";
            }
        } elseif ($_POST['action'] == 'edit') {
            $id = intval($_POST['id']);
            $name = sanitize_input($_POST['name']);
            $description = sanitize_input($_POST['description']);
            $price = floatval($_POST['price']);
            $category = sanitize_input($_POST['category']);
            $stock = intval($_POST['stock']);

            // Handle image upload
            $image_url = $_POST['existing_image_url']; // Use existing image if no new upload
            $update_image = false;

            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['image']['name'];
                $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                $filesize = $_FILES['image']['size'];

                // Validate file type
                if (in_array(strtolower($filetype), $allowed)) {
                    // Verify file is actually an image
                    if (getimagesize($_FILES['image']['tmp_name'])) {
                        // Limit file size to 2MB
                        if ($filesize <= 2000000) {
                            // Create unique filename
                            $new_filename = uniqid() . '.' . $filetype;
                            $upload_dir = '../uploads/products/';

                            // Create directory if it doesn't exist
                            if (!is_dir($upload_dir)) {
                                mkdir($upload_dir, 0777, true);
                            }

                            $target = $upload_dir . $new_filename;

                            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                                // Delete old image if it exists
                                if (!empty($_POST['existing_image_url']) && file_exists('../' . $_POST['existing_image_url'])) {
                                    unlink('../' . $_POST['existing_image_url']);
                                }

                                $image_url = 'uploads/products/' . $new_filename;
                                $update_image = true;
                            } else {
                                $message = "Gagal mengunggah gambar baru.";
                            }
                        } else {
                            $message = "Ukuran file terlalu besar. Maksimal 2MB.";
                        }
                    } else {
                        $message = "File bukan gambar yang valid.";
                    }
                } else {
                    $message = "Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.";
                }
            }

            if (empty($message)) {
                if ($update_image) {
                    $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, category=?, stock=?, image_url=? WHERE id=?");
                    $stmt->execute([$name, $description, $price, $category, $stock, $image_url, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, category=?, stock=? WHERE id=?");
                    $stmt->execute([$name, $description, $price, $category, $stock, $id]);
                }
                $message = "Produk berhasil diperbarui.";
            }
        } elseif ($_POST['action'] == 'delete') {
            $id = intval($_POST['id']);

            // Get product to delete the image file as well
            $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product && !empty($product['image_url']) && file_exists('../' . $product['image_url'])) {
                unlink('../' . $product['image_url']);
            }

            $stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
            $stmt->execute([$id]);
            $message = "Produk berhasil dihapus.";
        }
    }
}

// Get all products
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once '../includes/admin_header.php';
?>

<div class="max-w-7xl mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-dark">Daftar Produk</h2>
        <button onclick="openAddModal()" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-green-600 transition">
            <i class="fas fa-plus mr-2"></i>Tambah Produk
        </button>
    </div>
    
    <?php if ($message): ?>
        <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-md">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars(substr($product['description'], 0, 50)) . (strlen($product['description']) > 50 ? '...' : ''); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                <?php echo htmlspecialchars($product['category']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="<?php echo $product['stock'] > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo $product['stock']; ?> <?php echo $product['stock'] > 0 ? 'tersedia' : 'habis'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                            <button onclick="openEditModal(<?php echo $product['id']; ?>, 
                                '<?php echo addslashes(htmlspecialchars($product['name'])); ?>', 
                                '<?php echo addslashes(htmlspecialchars($product['description'])); ?>', 
                                <?php echo $product['price']; ?>, 
                                '<?php echo addslashes(htmlspecialchars($product['category'])); ?>', 
                                <?php echo $product['stock']; ?>, 
                                '<?php echo addslashes(htmlspecialchars($product['image_url'])); ?>')" 
                                class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="openDeleteModal(<?php echo $product['id']; ?>)" 
                                class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 id="modal-title" class="text-lg font-semibold text-dark">Tambah Produk Baru</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="product-form" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4">
                <input type="hidden" id="action" name="action" value="add">
                <input type="hidden" id="product-id" name="id" value="">
                <input type="hidden" id="existing-image-url" name="existing_image_url" value="">
                
                <div>
                    <label for="name" class="block text-gray-700 mb-2">Nama Produk</label>
                    <input type="text" id="name" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                
                <div>
                    <label for="description" class="block text-gray-700 mb-2">Deskripsi</label>
                    <textarea id="description" name="description" rows="3" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="price" class="block text-gray-700 mb-2">Harga (Rp)</label>
                        <input type="number" id="price" name="price" min="0" step="0.01" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    
                    <div>
                        <label for="category" class="block text-gray-700 mb-2">Kategori</label>
                        <input type="text" id="category" name="category" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="stock" class="block text-gray-700 mb-2">Stok</label>
                        <input type="number" id="stock" name="stock" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>

                    <div>
                        <label for="image" class="block text-gray-700 mb-2">Gambar Produk</label>
                        <input type="file" id="image" name="image" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        <p class="text-sm text-gray-500 mt-1">Format: JPG, PNG, GIF. Maksimal 2MB.</p>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-green-600">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-40 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-lg font-semibold text-red-600">Konfirmasi Hapus</h3>
                <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="mt-5 text-center">
                <i class="fas fa-exclamation-triangle text-4xl text-yellow-500 mb-4"></i>
                <p>Anda yakin ingin menghapus produk ini? Tindakan ini tidak dapat dibatalkan.</p>
                
                <form id="delete-form" method="POST" class="mt-6">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" id="delete-product-id" name="id" value="">
                    
                    <div class="flex justify-center space-x-3">
                        <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('modal-title').textContent = 'Tambah Produk Baru';
        document.getElementById('action').value = 'add';
        document.getElementById('product-form').reset();
        document.getElementById('product-id').value = '';
        document.getElementById('modal').classList.remove('hidden');
    }
    
    function openEditModal(id, name, description, price, category, stock, image_url) {
        document.getElementById('modal-title').textContent = 'Edit Produk';
        document.getElementById('action').value = 'edit';
        document.getElementById('product-id').value = id;
        document.getElementById('name').value = name;
        document.getElementById('description').value = description;
        document.getElementById('price').value = price;
        document.getElementById('category').value = category;
        document.getElementById('stock').value = stock;
        document.getElementById('existing-image-url').value = image_url;
        // Reset file input
        document.getElementById('image').value = '';
        document.getElementById('modal').classList.remove('hidden');
    }
    
    function closeModal() {
        document.getElementById('modal').classList.add('hidden');
    }
    
    function openDeleteModal(id) {
        document.getElementById('delete-product-id').value = id;
        document.getElementById('delete-modal').classList.remove('hidden');
    }
    
    function closeDeleteModal() {
        document.getElementById('delete-modal').classList.add('hidden');
    }
    
    // Close modals when clicking outside
    window.onclick = function(event) {
        var modal = document.getElementById('modal');
        var deleteModal = document.getElementById('delete-modal');
        
        if (event.target === modal) {
            closeModal();
        }
        
        if (event.target === deleteModal) {
            closeDeleteModal();
        }
    }
</script>

<?php include_once '../includes/admin_footer.php'; ?>