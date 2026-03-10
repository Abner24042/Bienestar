<?php
/**
 * Cargar configuración de EmailJS desde .env
 */
header('Content-Type: application/javascript');

require_once __DIR__ . '/../../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../');
$dotenv->load();

$serviceId = $_ENV['EMAILJS_SERVICE_ID'] ?? 'service_abc123';
$templateId = $_ENV['EMAILJS_TEMPLATE_ID'] ?? 'template_xyz789';
$cancellationTemplateId = $_ENV['EMAILJS_CANCELLATION_TEMPLATE_ID'] ?? 'template_cancelacion';
$professionalTemplateId = $_ENV['EMAILJS_PROFESSIONAL_TEMPLATE_ID'] ?? '';
$publicKey = $_ENV['EMAILJS_PUBLIC_KEY'] ?? 'xYz123AbC456';
?>
// Configuración de EmailJS cargada desde .env
const EMAIL_CONFIG = {
    serviceId: '<?php echo $serviceId; ?>',
    templateId: '<?php echo $templateId; ?>',
    cancellationTemplateId: '<?php echo $cancellationTemplateId; ?>',
    professionalTemplateId: '<?php echo $professionalTemplateId; ?>',
    publicKey: '<?php echo $publicKey; ?>'
};

// Verificar si EmailJS está configurado
const isEmailJSConfigured = () => {
    return EMAIL_CONFIG.serviceId !== 'service_abc123' &&
           EMAIL_CONFIG.templateId !== 'template_xyz789' &&
           EMAIL_CONFIG.publicKey !== 'xYz123AbC456';
};

// Inicializar EmailJS
if (typeof emailjs !== 'undefined') {
    emailjs.init(EMAIL_CONFIG.publicKey);
}
