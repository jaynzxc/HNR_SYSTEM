<?php
// C:\xampp\htdocs\HNR_SYSTEM\src\admin_portal\marketing\platform_edit.php
require_once 'models/Platform.php';

$platform = new Platform();
$platformData = null;
$isEdit = false;

// Check if ID is provided
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $platformData = $platform->getById($_GET['id']);
    $isEdit = true;
}

// Get all platform types for icons
$platformIcons = [
    'Foodpanda' => ['icon' => 'bag-shopping', 'color' => 'pink', 'bg' => 'pink-100', 'text' => 'pink-600'],
    'GrabFood' => ['icon' => 'motorcycle', 'color' => 'green', 'bg' => 'green-100', 'text' => 'green-600'],
    'Lalamove' => ['icon' => 'truck', 'color' => 'yellow', 'bg' => 'yellow-100', 'text' => 'yellow-600'],
    'GoFood' => ['icon' => 'utensils', 'color' => 'orange', 'bg' => 'orange-100', 'text' => 'orange-600'],
    'Deliveroo' => ['icon' => 'bicycle', 'color' => 'blue', 'bg' => 'blue-100', 'text' => 'blue-600'],
    'ShopeeFood' => ['icon' => 'shop', 'color' => 'purple', 'bg' => 'purple-100', 'text' => 'purple-600'],
    'WhatsApp' => ['icon' => 'square-whatsapp', 'color' => 'emerald', 'bg' => 'emerald-100', 'text' => 'emerald-600'],
    'Messenger' => ['icon' => 'facebook-messenger', 'color' => 'indigo', 'bg' => 'indigo-100', 'text' => 'indigo-600'],
    'default' => ['icon' => 'globe', 'color' => 'amber', 'bg' => 'amber-100', 'text' => 'amber-600']
];

// Get icon for current platform
$currentIcon = 'default';
if ($isEdit && isset($platformData['platform_name'])) {
    foreach ($platformIcons as $name => $iconData) {
        if (stripos($platformData['platform_name'], $name) !== false) {
            $currentIcon = $name;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Edit' : 'Add New'; ?> Platform · Online Ordering</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .transition-all { transition: all 0.3s ease; }
        .hover-scale:hover { transform: scale(1.02); }
        .glass-effect { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen">

    <!-- Main Container -->
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        
        <!-- Header with Back Button -->
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="online_ordering_integration.php" 
                   class="group flex items-center gap-2 text-slate-600 hover:text-amber-600 transition-all bg-white px-4 py-2 rounded-xl shadow-sm hover:shadow-md">
                    <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                    <span>Back to Dashboard</span>
                </a>
                <h1 class="text-2xl font-semibold text-slate-800 hidden sm:block">
                    <?php echo $isEdit ? 'Edit Platform' : 'Add New Platform'; ?>
                </h1>
            </div>
            
            <?php if ($isEdit): ?>
            <div class="flex items-center gap-2 bg-amber-50 text-amber-700 px-4 py-2 rounded-xl">
                <i class="fa-regular fa-pen-to-square"></i>
                <span class="text-sm font-medium">Editing Mode</span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Mobile Title -->
        <h1 class="text-2xl font-semibold text-slate-800 mb-4 sm:hidden">
            <?php echo $isEdit ? 'Edit Platform' : 'Add New Platform'; ?>
        </h1>

        <?php if ($isEdit && !$platformData): ?>
        <!-- Error State -->
        <div class="bg-white rounded-2xl shadow-lg border border-red-200 p-8 text-center">
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-exclamation-triangle text-3xl text-red-500"></i>
            </div>
            <h2 class="text-xl font-semibold text-slate-800 mb-2">Platform Not Found</h2>
            <p class="text-slate-500 mb-6">The platform you're trying to edit doesn't exist or has been removed.</p>
            <a href="online_ordering_integration.php" 
               class="inline-flex items-center gap-2 bg-amber-600 text-white px-6 py-3 rounded-xl hover:bg-amber-700 transition-all">
                <i class="fa-solid fa-arrow-left"></i>
                Return to Dashboard
            </a>
        </div>
        <?php else: ?>

        <!-- Main Form Card -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
            
            <!-- Form Header with Icon Preview -->
            <div class="bg-gradient-to-r from-amber-50 to-orange-50 px-6 py-4 border-b border-slate-200">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-<?php echo $platformIcons[$currentIcon]['bg']; ?> flex items-center justify-center text-<?php echo $platformIcons[$currentIcon]['text']; ?> text-2xl shadow-lg">
                        <i class="fa-solid fa-<?php echo $platformIcons[$currentIcon]['icon']; ?>"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-slate-800">
                            <?php echo $isEdit ? $platformData['platform_name'] : 'Platform Details'; ?>
                        </h2>
                        <p class="text-sm text-slate-500">
                            <?php echo $isEdit ? 'Update your platform configuration below' : 'Fill in the information to connect a new platform'; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form Body -->
            <form id="platformForm" method="POST" class="p-6 space-y-6">
                <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?php echo $platformData['id']; ?>">
                <?php endif; ?>

                <!-- Platform Name & Icon Preview -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            <i class="fa-regular fa-building mr-1 text-amber-600"></i>
                            Platform Name *
                        </label>
                        <input type="text" 
                               name="platform_name" 
                               id="platform_name"
                               required 
                               value="<?php echo htmlspecialchars($platformData['platform_name'] ?? ''); ?>"
                               placeholder="e.g., Foodpanda, GrabFood, etc."
                               class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            <i class="fa-regular fa-image mr-1 text-amber-600"></i>
                            Icon Preview
                        </label>
                        <div id="iconPreview" class="h-12 w-12 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600 text-xl">
                            <i class="fa-solid fa-globe"></i>
                        </div>
                    </div>
                </div>

                <!-- Platform Type and Status -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            <i class="fa-regular fa-tag mr-1 text-amber-600"></i>
                            Platform Type
                        </label>
                        <select name="platform_type" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white">
                            <option value="delivery" <?php echo ($platformData['platform_type'] ?? '') == 'delivery' ? 'selected' : ''; ?>>🚚 Delivery</option>
                            <option value="on-demand" <?php echo ($platformData['platform_type'] ?? '') == 'on-demand' ? 'selected' : ''; ?>>⚡ On-demand</option>
                            <option value="direct" <?php echo ($platformData['platform_type'] ?? '') == 'direct' ? 'selected' : ''; ?>>🌐 Direct / Website</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            <i class="fa-regular fa-circle-check mr-1 text-amber-600"></i>
                            Status
                        </label>
                        <select name="status" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white">
                            <option value="connected" <?php echo ($platformData['status'] ?? '') == 'connected' ? 'selected' : ''; ?>>🟢 Connected</option>
                            <option value="disconnected" <?php echo ($platformData['status'] ?? '') == 'disconnected' ? 'selected' : ''; ?>>🔴 Disconnected</option>
                            <option value="pending" <?php echo ($platformData['status'] ?? '') == 'pending' ? 'selected' : ''; ?>>🟡 Pending</option>
                        </select>
                    </div>
                </div>

                <!-- Commission Rate -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            <i class="fa-regular fa-percent mr-1 text-amber-600"></i>
                            Commission Rate (%)
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   step="0.01" 
                                   name="commission_rate" 
                                   value="<?php echo htmlspecialchars($platformData['commission_rate'] ?? 0); ?>"
                                   class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent pl-12">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">%</span>
                        </div>
                    </div>
                    <div class="flex items-end">
                        <div class="bg-slate-50 p-3 rounded-xl w-full">
                            <span class="text-xs text-slate-500">Estimated fee on ₱1000 order</span>
                            <div class="text-lg font-semibold text-amber-600" id="estimatedFee">
                                ₱<?php echo number_format(($platformData['commission_rate'] ?? 0) * 10, 2); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- API Configuration Section -->
                <div class="border-t border-slate-200 pt-6">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-key text-amber-600"></i>
                        API Configuration
                        <span class="text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded-full">Optional</span>
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                <i class="fa-regular fa-key mr-1 text-amber-600"></i>
                                API Key
                            </label>
                            <div class="relative">
                                <input type="text" 
                                       name="api_key" 
                                       id="api_key"
                                       value="<?php echo htmlspecialchars($platformData['api_key'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent pr-24">
                                <button type="button" 
                                        onclick="toggleVisibility('api_key')"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-amber-600 px-3 py-1">
                                    <i class="fa-regular fa-eye-slash" id="api_key_icon"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                <i class="fa-regular fa-lock mr-1 text-amber-600"></i>
                                API Secret
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       name="api_secret" 
                                       id="api_secret"
                                       value="<?php echo htmlspecialchars($platformData['api_secret'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent pr-24">
                                <button type="button" 
                                        onclick="toggleVisibility('api_secret')"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-amber-600 px-3 py-1">
                                    <i class="fa-regular fa-eye-slash" id="api_secret_icon"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                <i class="fa-regular fa-link mr-1 text-amber-600"></i>
                                Webhook URL
                            </label>
                            <input type="url" 
                                   name="webhook_url" 
                                   value="<?php echo htmlspecialchars($platformData['webhook_url'] ?? ''); ?>"
                                   placeholder="https://your-domain.com/webhook"
                                   class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Hidden fields for styling -->
                <input type="hidden" name="icon_class" id="icon_class" value="<?php echo htmlspecialchars($platformData['icon_class'] ?? $platformIcons[$currentIcon]['icon']); ?>">
                <input type="hidden" name="bg_color" id="bg_color" value="<?php echo htmlspecialchars($platformData['bg_color'] ?? $platformIcons[$currentIcon]['bg']); ?>">

                <!-- Form Actions -->
                <div class="border-t border-slate-200 pt-6 flex flex-col sm:flex-row gap-3 justify-between items-center">
                    <div class="flex gap-3 w-full sm:w-auto">
                        <button type="submit" 
                                class="flex-1 sm:flex-none bg-gradient-to-r from-amber-600 to-amber-700 text-white px-8 py-3 rounded-xl hover:from-amber-700 hover:to-amber-800 transition-all shadow-lg hover:shadow-xl font-medium">
                            <i class="fa-regular fa-floppy-disk mr-2"></i>
                            <?php echo $isEdit ? 'Save Changes' : 'Add Platform'; ?>
                        </button>
                        
                        <?php if (!$isEdit): ?>
                        <button type="button" 
                                onclick="saveAndAddAnother()"
                                class="flex-1 sm:flex-none border-2 border-amber-600 text-amber-700 px-6 py-3 rounded-xl hover:bg-amber-50 transition-all font-medium">
                            <i class="fa-regular fa-plus mr-2"></i>
                            Save & Add Another
                        </button>
                        <?php endif; ?>
                    </div>
                    
                    <a href="online_ordering_integration.php" 
                       class="text-slate-500 hover:text-slate-700 transition-colors flex items-center gap-2">
                        <i class="fa-regular fa-xmark"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Tips Card -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-start gap-3">
            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 flex-shrink-0">
                <i class="fa-regular fa-lightbulb"></i>
            </div>
            <div>
                <h4 class="font-semibold text-blue-800 mb-1">Quick Tips</h4>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• Commission rate is calculated automatically on each order</li>
                    <li>• API credentials are encrypted before storing</li>
                    <li>• Webhook URL should be publicly accessible</li>
                    <li>• Test the connection after saving changes</li>
                </ul>
            </div>
        </div>

        <?php endif; ?>
    </div>

    <script>
    // Auto-update icon preview based on platform name
    document.getElementById('platform_name')?.addEventListener('input', function(e) {
        const name = e.target.value.toLowerCase();
        const iconPreview = document.getElementById('iconPreview');
        const iconClass = document.getElementById('icon_class');
        const bgColor = document.getElementById('bg_color');
        
        // Simple icon mapping based on name
        if (name.includes('foodpanda')) {
            iconPreview.innerHTML = '<i class="fa-solid fa-bag-shopping"></i>';
            iconPreview.className = 'h-12 w-12 rounded-xl bg-pink-100 flex items-center justify-center text-pink-600 text-xl';
            iconClass.value = 'bag-shopping';
            bgColor.value = 'pink-100';
        } else if (name.includes('grab')) {
            iconPreview.innerHTML = '<i class="fa-solid fa-motorcycle"></i>';
            iconPreview.className = 'h-12 w-12 rounded-xl bg-green-100 flex items-center justify-center text-green-600 text-xl';
            iconClass.value = 'motorcycle';
            bgColor.value = 'green-100';
        } else if (name.includes('lalamove')) {
            iconPreview.innerHTML = '<i class="fa-solid fa-truck"></i>';
            iconPreview.className = 'h-12 w-12 rounded-xl bg-yellow-100 flex items-center justify-center text-yellow-600 text-xl';
            iconClass.value = 'truck';
            bgColor.value = 'yellow-100';
        } else if (name.includes('whatsapp')) {
            iconPreview.innerHTML = '<i class="fa-brands fa-square-whatsapp"></i>';
            iconPreview.className = 'h-12 w-12 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 text-xl';
            iconClass.value = 'square-whatsapp';
            bgColor.value = 'emerald-100';
        } else if (name.includes('messenger')) {
            iconPreview.innerHTML = '<i class="fa-brands fa-facebook-messenger"></i>';
            iconPreview.className = 'h-12 w-12 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600 text-xl';
            iconClass.value = 'facebook-messenger';
            bgColor.value = 'indigo-100';
        } else {
            iconPreview.innerHTML = '<i class="fa-solid fa-globe"></i>';
            iconPreview.className = 'h-12 w-12 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600 text-xl';
            iconClass.value = 'globe';
            bgColor.value = 'amber-100';
        }
    });

    // Calculate estimated fee
    document.querySelector('input[name="commission_rate"]')?.addEventListener('input', function(e) {
        const rate = parseFloat(e.target.value) || 0;
        const estimated = rate * 10;
        document.getElementById('estimatedFee').textContent = '₱' + estimated.toFixed(2);
    });

    // Toggle password visibility
    function toggleVisibility(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '_icon');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.className = 'fa-regular fa-eye';
        } else {
            field.type = 'password';
            icon.className = 'fa-regular fa-eye-slash';
        }
    }

    // Handle form submission
    document.getElementById('platformForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        
        // Show loading state
        submitButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Saving...';
        submitButton.disabled = true;
        
        fetch('api/update_platform.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showNotification('success', data.message);
                
                // Redirect after a short delay
                setTimeout(() => {
                    window.location.href = 'online_ordering_integration.php';
                }, 1500);
            } else {
                showNotification('error', data.message);
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
            }
        })
        .catch(error => {
            showNotification('error', 'An error occurred: ' + error);
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        });
    });

    // Save and add another
    function saveAndAddAnother() {
        const form = document.getElementById('platformForm');
        const formData = new FormData(form);
        
        fetch('api/update_platform.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', 'Platform saved! You can add another.');
                form.reset();
                // Reset icon preview
                document.getElementById('iconPreview').innerHTML = '<i class="fa-solid fa-globe"></i>';
                document.getElementById('iconPreview').className = 'h-12 w-12 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600 text-xl';
            } else {
                showNotification('error', data.message);
            }
        })
        .catch(error => {
            showNotification('error', 'An error occurred: ' + error);
        });
    }

    // Notification system
    function showNotification(type, message) {
        // Remove existing notification
        const existing = document.querySelector('.notification-toast');
        if (existing) existing.remove();
        
        // Create notification
        const notification = document.createElement('div');
        notification.className = `notification-toast fixed top-4 right-4 ${type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'} border rounded-xl p-4 shadow-lg z-50 animate-slideIn`;
        notification.innerHTML = `
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 ${type === 'success' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'} rounded-full flex items-center justify-center">
                    <i class="fa-regular ${type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'}"></i>
                </div>
                <p class="font-medium">${message}</p>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Add animation style
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        .animate-slideIn {
            animation: slideIn 0.3s ease-out;
        }
    `;
    document.head.appendChild(style);
    </script>
</body>
</html>