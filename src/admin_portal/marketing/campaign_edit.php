<?php
// C:\xampp\htdocs\HNR_SYSTEM\src\admin_portal\marketing\campaign_edit.php
require_once 'models/Campaign.php';

$campaign = new Campaign();
$campaignData = null;
$isEdit = false;

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $campaignData = $campaign->getById($_GET['id']);
    $isEdit = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Edit' : 'Create'; ?> Campaign · Marketing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-3xl">
        <!-- Header with Back Button -->
        <div class="mb-6 flex items-center gap-4">
            <a href="hotelmarketing_&_promotions.php" 
               class="group flex items-center gap-2 text-slate-600 hover:text-amber-600 transition-all bg-white px-4 py-2 rounded-xl shadow-sm hover:shadow-md border border-slate-200">
                <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                <span>Back to Campaigns</span>
            </a>
            <h1 class="text-2xl font-semibold text-slate-800">
                <?php echo $isEdit ? 'Edit Campaign' : 'Create New Campaign'; ?>
            </h1>
        </div>

        <!-- Campaign Form -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
            <form id="campaignForm" method="POST" action="api/update_campaign.php" class="p-6 space-y-6">
                <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?php echo $campaignData['id']; ?>">
                <?php endif; ?>

                <!-- Campaign Name -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        <i class="fa-regular fa-megaphone text-amber-600 mr-1"></i>
                        Campaign Name *
                    </label>
                    <input type="text" name="campaign_name" required
                           value="<?php echo htmlspecialchars($campaignData['campaign_name'] ?? ''); ?>"
                           placeholder="e.g., Summer Escape, Weekend Getaway"
                           class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        <i class="fa-regular fa-file-lines text-amber-600 mr-1"></i>
                        Description
                    </label>
                    <textarea name="description" rows="3"
                              placeholder="Describe your campaign offer..."
                              class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent"><?php echo htmlspecialchars($campaignData['description'] ?? ''); ?></textarea>
                </div>

                <!-- Campaign Type and Status -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Campaign Type</label>
                        <select name="campaign_type" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                            <option value="discount" <?php echo ($campaignData['campaign_type'] ?? '') == 'discount' ? 'selected' : ''; ?>>🎯 Discount</option>
                            <option value="package" <?php echo ($campaignData['campaign_type'] ?? '') == 'package' ? 'selected' : ''; ?>>📦 Package</option>
                            <option value="gift" <?php echo ($campaignData['campaign_type'] ?? '') == 'gift' ? 'selected' : ''; ?>>🎁 Gift / Free Item</option>
                            <option value="event" <?php echo ($campaignData['campaign_type'] ?? '') == 'event' ? 'selected' : ''; ?>>🎪 Event</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                            <option value="draft" <?php echo ($campaignData['status'] ?? '') == 'draft' ? 'selected' : ''; ?>>📝 Draft</option>
                            <option value="scheduled" <?php echo ($campaignData['status'] ?? '') == 'scheduled' ? 'selected' : ''; ?>>📅 Scheduled</option>
                            <option value="active" <?php echo ($campaignData['status'] ?? '') == 'active' ? 'selected' : ''; ?>>✨ Active</option>
                            <option value="ended" <?php echo ($campaignData['status'] ?? '') == 'ended' ? 'selected' : ''; ?>>✅ Ended</option>
                        </select>
                    </div>
                </div>

                <!-- Discount Details -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Discount Type</label>
                        <select name="discount_type" class="w-full px-4 py-3 border border-slate-300 rounded-xl">
                            <option value="percentage" <?php echo ($campaignData['discount_type'] ?? '') == 'percentage' ? 'selected' : ''; ?>>Percentage (%)</option>
                            <option value="fixed" <?php echo ($campaignData['discount_type'] ?? '') == 'fixed' ? 'selected' : ''; ?>>Fixed Amount (₱)</option>
                            <option value="free_item" <?php echo ($campaignData['discount_type'] ?? '') == 'free_item' ? 'selected' : ''; ?>>Free Item</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Discount Value</label>
                        <input type="number" step="0.01" name="discount_value"
                               value="<?php echo htmlspecialchars($campaignData['discount_value'] ?? 0); ?>"
                               class="w-full px-4 py-3 border border-slate-300 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Target Audience</label>
                        <input type="text" name="target_audience"
                               value="<?php echo htmlspecialchars($campaignData['target_audience'] ?? ''); ?>"
                               placeholder="e.g., All guests, Spa visitors"
                               class="w-full px-4 py-3 border border-slate-300 rounded-xl">
                    </div>
                </div>

                <!-- Dates -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Start Date</label>
                        <input type="date" name="start_date"
                               value="<?php echo htmlspecialchars($campaignData['start_date'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-slate-300 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">End Date</label>
                        <input type="date" name="end_date"
                               value="<?php echo htmlspecialchars($campaignData['end_date'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-slate-300 rounded-xl">
                    </div>
                </div>

                <!-- Targets -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Target Redemptions</label>
                        <input type="number" name="target_redemptions"
                               value="<?php echo htmlspecialchars($campaignData['target_redemptions'] ?? 0); ?>"
                               class="w-full px-4 py-3 border border-slate-300 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Budget (₱)</label>
                        <input type="number" step="0.01" name="budget"
                               value="<?php echo htmlspecialchars($campaignData['budget'] ?? 0); ?>"
                               class="w-full px-4 py-3 border border-slate-300 rounded-xl">
                    </div>
                </div>

                <!-- Hidden fields for styling -->
                <input type="hidden" name="bg_color" value="<?php echo htmlspecialchars($campaignData['bg_color'] ?? 'green-100'); ?>">
                <input type="hidden" name="text_color" value="<?php echo htmlspecialchars($campaignData['text_color'] ?? 'green-700'); ?>">

                <!-- Form Actions -->
                <div class="border-t border-slate-200 pt-6 flex gap-3 justify-end">
                    <a href="hotelmarketing_&_promotions.php" 
                       class="px-6 py-3 border border-slate-300 rounded-xl hover:bg-slate-50 transition">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="bg-amber-600 text-white px-8 py-3 rounded-xl hover:bg-amber-700 transition shadow-lg">
                        <i class="fa-regular fa-floppy-disk mr-2"></i>
                        <?php echo $isEdit ? 'Save Changes' : 'Create Campaign'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.getElementById('campaignForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Saving...';
        submitBtn.disabled = true;
        
        fetch('api/update_campaign.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.href = 'hotelmarketing_&_promotions.php';
            } else {
                alert('Error: ' + data.message);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            alert('Error: ' + error);
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
    </script>
</body>
</html>