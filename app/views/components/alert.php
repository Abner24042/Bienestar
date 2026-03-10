<?php
/**
 * Componente de Alerta
 * Uso: include 'components/alert.php';
 */

if (!isset($alertType)) $alertType = 'info';
if (!isset($alertMessage)) $alertMessage = '';
if (!isset($alertDismissible)) $alertDismissible = true;

$alertIcons = [
    'success' => '✓',
    'error' => '✕',
    'warning' => '⚠',
    'info' => 'ℹ'
];

$icon = $alertIcons[$alertType] ?? $alertIcons['info'];
?>

<div class="alert alert-<?php echo $alertType; ?> <?php echo $alertDismissible ? 'alert-dismissible' : ''; ?>">
    <span class="alert-icon"><?php echo $icon; ?></span>
    <span class="alert-message"><?php echo htmlspecialchars($alertMessage); ?></span>
    <?php if ($alertDismissible): ?>
        <button class="alert-close" type="button">&times;</button>
    <?php endif; ?>
</div>

<style>
.alert {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 0.9rem;
    position: relative;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-warning {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.alert-info {
    background-color: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.alert-icon {
    font-size: 1.2rem;
    font-weight: bold;
}

.alert-message {
    flex: 1;
}

.alert-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: inherit;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0.7;
    transition: opacity 0.2s;
}

.alert-close:hover {
    opacity: 1;
}
</style>