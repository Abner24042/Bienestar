/**
 * Enviar email de confirmación de cita
 */
async function sendAppointmentEmail(appointmentData) {
    const templateParams = {
        to_name: appointmentData.userName,
        to_email: appointmentData.userEmail,
        appointment_date: appointmentData.date,
        appointment_time: appointmentData.time,
        appointment_type: appointmentData.type,
        doctor_name: appointmentData.doctorName,
        appointment_notes: appointmentData.notes || 'Sin notas adicionales'
    };

    try {
        const response = await emailjs.send(
            EMAIL_CONFIG.serviceId,
            EMAIL_CONFIG.templateId,
            templateParams,
            EMAIL_CONFIG.publicKey
        );

        console.log('✅ Email enviado:', response);
        return { success: true, message: 'Email enviado correctamente' };
    } catch (error) {
        console.error('❌ Error al enviar email:', error);
        return { success: false, message: 'Error al enviar email', error };
    }
}

/**
 * Enviar email de cancelación de cita
 */
async function sendCancellationEmail(appointmentData) {
    const templateParams = {
        to_name: appointmentData.userName,
        to_email: appointmentData.userEmail,
        appointment_title: appointmentData.titulo,
        appointment_date: appointmentData.date,
        appointment_time: appointmentData.time
    };

    try {
        const response = await emailjs.send(
            EMAIL_CONFIG.serviceId,
            EMAIL_CONFIG.cancellationTemplateId || 'template_cancelacion',
            templateParams,
            EMAIL_CONFIG.publicKey
        );

        console.log('✅ Email de cancelación enviado:', response);
        return { success: true, message: 'Email de cancelación enviado' };
    } catch (error) {
        console.error('❌ Error al enviar email de cancelación:', error);
        return { success: false, message: 'Error al enviar email', error };
    }
}

/**
 * Enviar email cuando un profesional agenda una cita para un usuario
 */
async function sendProfessionalAppointmentEmail(data) {
    const templateParams = {
        to_name: data.userName,
        to_email: data.userEmail,
        appointment_date: data.date,
        appointment_time: data.time,
        appointment_title: data.title,
        professional_name: data.professionalName,
        professional_role: data.professionalRole,
        appointment_notes: data.description || 'Sin notas adicionales'
    };

    try {
        const templateId = EMAIL_CONFIG.professionalTemplateId || EMAIL_CONFIG.templateId;
        const response = await emailjs.send(
            EMAIL_CONFIG.serviceId,
            templateId,
            templateParams,
            EMAIL_CONFIG.publicKey
        );

        console.log('Email de cita profesional enviado:', response);
        return { success: true };
    } catch (error) {
        console.error('Error al enviar email de cita profesional:', error);
        return { success: false, error };
    }
}

/**
 * Enviar email de recordatorio de cita
 */
async function sendAppointmentReminder(appointmentData) {
    const templateParams = {
        to_name: appointmentData.userName,
        to_email: appointmentData.userEmail,
        appointment_date: appointmentData.date,
        appointment_time: appointmentData.time,
        appointment_type: appointmentData.type
    };

    try {
        const response = await emailjs.send(
            EMAIL_CONFIG.serviceId,
            'template_recordatorio', // Necesitas crear esta plantilla
            templateParams,
            EMAIL_CONFIG.publicKey
        );

        return { success: true };
    } catch (error) {
        console.error('Error:', error);
        return { success: false, error };
    }
}