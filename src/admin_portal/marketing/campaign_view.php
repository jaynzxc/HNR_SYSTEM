<?php
// C:\xampp\htdocs\HNR_SYSTEM\src\admin_portal\marketing\campaign_view.php
require_once 'models/Campaign.php';
require_once 'models/PromoCode.php';

$campaign = new Campaign();
$promoCode = new PromoCode();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: hotelmarketing_&_promotions.php');
    exit;
}

$campaignData = $campaign->getById($_GET['id']);
if (!$campaignData) {
    header('Location: hotelmarketing_&_promotions.php');
    exit;
}

$promoCodes = $promoCode->getByCampaign($_GET['id']);
$progress = $campaignData['target_redemptions'] > 0 ? 
    min(100, round(($campaignData['current_redemptions'] / $campaignData['target_redemptions']) * 100)) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Campaign · <?php echo htmlspecialchars($campaignData['campaign_name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header -->
        <div class="mb-6 flex items-center gap-4">
            <a href="hotelmarketing_&_promotions.php" 
               class="group flex items-center gap-2 text-slate-600 hover:text-amber-600 transition-all bg-white px-4 py-2 rounded-xl shadow-sm hover:shadow-md border border-slate-200">
                <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                <span>Back to Campaigns</span>
            </a>
            <h1 class="text-2xl font-semibold text-slate-800">Campaign Details</h1>
        </div>

        <!-- Campaign Overview -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden mb-6">
            <div class="p-6 border-b border-slate-200 bg-gradient-to-r from-amber-50 to-orange-50">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-800"><?php echo htmlspecialchars($campaignData['campaign_name']); ?></h2>
                        <p class="text-slate-600 mt-1"><?php echo htmlspecialchars($campaignData['description']); ?></p>
                    </div>
                    <span class="px-4 py-2 <?php 
                        echo $campaignData['status'] == 'active' ? 'bg-green-100 text-green-700' : 
                            ($campaignData['status'] == 'scheduled' ? 'bg-blue-100 text-blue-700' : 
                            'bg-slate-100 text-slate-600'); 
                    ?> rounded-full text-sm font-medium">
                        <?php echo ucfirst($campaignData['status']); ?>
                    </span>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Campaign Details -->
                <div class="space-y-4">
                    <h3 class="font-semibold text-lg flex items-center gap-2">
                        <i class="fa-regular fa-circle-info text-amber-600"></i>
                        Campaign Information
                    </h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-slate-500">Type</p>
                            <p class="font-medium"><?php echo ucfirst($campaignData['campaign_type']); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Target Audience</p>
                            <p class="font-medium"><?php echo $campaignData['target_audience'] ?: 'All guests'; ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Start Date</p>
                            <p class="font-medium"><?php echo date('M d, Y', strtotime($campaignData['start_date'])); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">End Date</p>
                            <p class="font-medium"><?php echo date('M d, Y', strtotime($campaignData['end_date'])); ?></p>
                        </div>
                    </div>

                    <?php if ($campaignData['discount_type']): ?>
                    <div class="bg-slate-50 p-4 rounded-xl">
                        <p class="text-sm text-slate-500 mb-1">Discount Offer</p>
                        <p class="text-lg font-bold text-amber-600">
                            <?php 
                            if ($campaignData['discount_type'] == 'percentage') {
                                echo $campaignData['discount_value'] . '% off';
                            } elseif ($campaignData['discount_type'] == 'fixed') {
                                echo '₱' . number_format($campaignData['discount_value']) . ' off';
                            } else {
                                echo 'Free Item';
                            }
                            ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Performance Metrics -->
                <div class="space-y-4">
                    <h3 class="font-semibold text-lg flex items-center gap-2">
                        <i class="fa-regular fa-chart-line text-amber-600"></i>
                        Performance Metrics
                    </h3>

                    <div class="space-y-3">
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-slate-600">Redemptions</span>
                                <span class="font-semibold"><?php echo $campaignData['current_redemptions']; ?> / <?php echo $campaignData['target_redemptions']; ?></span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-3">
                                <div class="bg-amber-500 h-3 rounded-full" style="width: <?php echo $progress; ?>%"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <div class="bg-green-50 p-3 rounded-xl">
                                <p class="text-xs text-green-600">Revenue Generated</p>
                                <p class="text-lg font-bold text-green-700">₱<?php echo number_format($campaignData['revenue_generated']); ?></p>
                            </div>
                            <div class="bg-blue-50 p-3 rounded-xl">
                                <p class="text-xs text-blue-600">Budget</p>
                                <p class="text-lg font-bold text-blue-700">₱<?php echo number_format($campaignData['budget']); ?></p>
                            </div>
                        </div>

                        <div class="bg-purple-50 p-4 rounded-xl">
                            <p class="text-xs text-purple-600 mb-1">Return on Investment (ROI)</p>
                            <p class="text-2xl font-bold <?php echo $campaignData['roi'] > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo number_format($campaignData['roi'], 1); ?>%
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Promo Codes Section -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-200 flex justify-between items-center">
                <h3 class="font-semibold text-lg flex items-center gap-2">
                    <i class="fa-regular fa-ticket text-amber-600"></i>
                    Promo Codes
                </h3>
                <a href="promo_edit.php?campaign=<?php echo $campaignData['id']; ?>" 
                   class="text-sm bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 transition">
                    <i class="fa-regular fa-plus mr-1"></i> Add Promo Code
                </a>
            </div>

            <div class="p-6">
                <?php if (empty($promoCodes)): ?>
                <p class="text-center text-slate-500 py-8">No promo codes created for this campaign yet.</p>
                <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($promoCodes as $promo): ?>
                    <div class="flex justify-between items-center p-4 bg-slate-50 rounded-xl">
                        <div>
                            <span class="font-mono font-bold text-lg bg-white px-3 py-1 rounded-lg border border-slate-200">
                                <?php echo $promo['code']; ?>
                            </span>
                            <p class="text-sm text-slate-500 mt-1"><?php echo $promo['description']; ?></p>
                        </div>
                        <div class="text-right">
                            <span class="bg-<?php echo $promo['current_uses'] < $promo['max_uses'] ? 'green' : 'yellow'; ?>-100 text-<?php echo $promo['current_uses'] < $promo['max_uses'] ? 'green' : 'yellow'; ?>-700 px-3 py-1 rounded-full text-sm">
                                <?php echo $promo['current_uses']; ?>/<?php echo $promo['max_uses']; ?> used
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>