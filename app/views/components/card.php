<?php
/**
 * Componente de Card
 * Uso: include 'components/card.php';
 */

if (!isset($cardTitle)) $cardTitle = '';
if (!isset($cardContent)) $cardContent = '';
if (!isset($cardImage)) $cardImage = null;
if (!isset($cardLink)) $cardLink = null;
if (!isset($cardClass)) $cardClass = '';
?>

<div class="card <?php echo $cardClass; ?>">
    <?php if ($cardImage): ?>
        <div class="card-image">
            <img src="<?php echo htmlspecialchars($cardImage); ?>" alt="<?php echo htmlspecialchars($cardTitle); ?>">
        </div>
    <?php endif; ?>
    
    <div class="card-body">
        <?php if ($cardTitle): ?>
            <h3 class="card-title"><?php echo htmlspecialchars($cardTitle); ?></h3>
        <?php endif; ?>
        
        <div class="card-content">
            <?php echo $cardContent; ?>
        </div>
        
        <?php if ($cardLink): ?>
            <div class="card-footer">
                <a href="<?php echo htmlspecialchars($cardLink); ?>" class="card-link">
                    Ver más →
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.card-image {
    width: 100%;
    height: 200px;
    overflow: hidden;
}

.card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.card:hover .card-image img {
    transform: scale(1.1);
}

.card-body {
    padding: 20px;
}

.card-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 10px;
    color: #111;
}

.card-content {
    color: #666;
    line-height: 1.6;
    margin-bottom: 15px;
}

.card-footer {
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.card-link {
    color: #ff6b35;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: gap 0.2s;
}

.card-link:hover {
    gap: 10px;
}
</style>