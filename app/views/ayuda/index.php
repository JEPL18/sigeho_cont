<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-question-circle-fill me-2"></i> Ayuda de SIGEHO-CONT</h2>
    </div>
    <p class="text-muted">SIGEHO-CONT facilita la construcción de horarios de Contaduría previniendo choques entre profesores, aulas y horas. Esta guía explica cada módulo y los mensajes que muestra el sistema.</p>

    <div class="accordion" id="ayudaAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="ayudaH1">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#ayudaC1" aria-expanded="true" aria-controls="ayudaC1">Panel Principal</button>
            </h2>
            <div id="ayudaC1" class="accordion-collapse collapse show" role="region" aria-labelledby="ayudaH1" data-bs-parent="#ayudaAccordion">
                <div class="accordion-body">
                    Muestra la parrilla semanal de una sección (lunes a sábado, 7:00 am a 7:00 pm) con los bloques de clase. Use los filtros de sección y turno para cambiar lo que se muestra. Debajo aparece el historial de choques detectados: cada intento bloqueado queda registrado. Solo el administrador puede vaciar ese historial.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="ayudaH2">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ayudaC2" aria-expanded="false" aria-controls="ayudaC2">Profesores</button>
            </h2>
            <div id="ayudaC2" class="accordion-collapse collapse" role="region" aria-labelledby="ayudaH2" data-bs-parent="#ayudaAccordion">
                <div class="accordion-body">
                    Registre el nombre completo (el título va dentro del nombre, por ejemplo "Dr. Alexis Mujica"), la cédula de 7 u 8 números y el estado. Solo un profesor ACTIVO puede aparecer en un horario. En el formulario puede marcar su disponibilidad por día (lunes a sábado) y turno (matutino, vespertino, nocturno) y las materias que dicta. Si un profesor no tiene disponibilidad ni materias registradas, el sistema no lo restringe; cuando sí tiene datos, un horario fuera de su disponibilidad o con una materia que no dicta se bloquea con un mensaje.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="ayudaH3">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ayudaC3" aria-expanded="false" aria-controls="ayudaC3">Malla Curricular y prelaciones</button>
            </h2>
            <div id="ayudaC3" class="accordion-collapse collapse" role="region" aria-labelledby="ayudaH3" data-bs-parent="#ayudaAccordion">
                <div class="accordion-body">
                    Registre cada materia con su código (por ejemplo CON-221) y su trayecto. En el formulario puede marcar sus prelaciones, es decir, el orden del pensum. Las prelaciones son solo un registro de orden: no bloquean la creación de horarios, porque el sistema no maneja estudiantes ni notas. No se permite una materia como prelación de sí misma, ni ciclos entre materias, ni eliminar una materia que es prelación de otra.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="ayudaH4">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ayudaC4" aria-expanded="false" aria-controls="ayudaC4">Aulas y Espacios</button>
            </h2>
            <div id="ayudaC4" class="accordion-collapse collapse" role="region" aria-labelledby="ayudaH4" data-bs-parent="#ayudaAccordion">
                <div class="accordion-body">
                    Registre el código del aula (por ejemplo M-110), su capacidad de alumnos y su estado. Solo un aula OPERATIVA puede usarse en un horario. En "Opciones avanzadas" puede escribir los recursos disponibles como texto libre separado por comas (por ejemplo "Proyector, Aire acondicionado, Pizarra acrílica"), con un máximo de 15. La capacidad es el número real de puestos del aula y no depende de los recursos.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="ayudaH5">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ayudaC5" aria-expanded="false" aria-controls="ayudaC5">Secciones</button>
            </h2>
            <div id="ayudaC5" class="accordion-collapse collapse" role="region" aria-labelledby="ayudaH5" data-bs-parent="#ayudaAccordion">
                <div class="accordion-body">
                    Registre el código de la sección (por ejemplo SECC-34), su trayecto, trimestre y matrícula (cantidad de alumnos). La matrícula se compara con la capacidad del aula al crear un horario: si la matrícula supera la capacidad, el horario no se guarda.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="ayudaH6">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ayudaC6" aria-expanded="false" aria-controls="ayudaC6">Horarios</button>
            </h2>
            <div id="ayudaC6" class="accordion-collapse collapse" role="region" aria-labelledby="ayudaH6" data-bs-parent="#ayudaAccordion">
                <div class="accordion-body">
                    Al crear un horario el sistema valida, en este orden: que el profesor esté ACTIVO, que el aula esté OPERATIVA, que la matrícula de la sección quepa en la capacidad del aula, que el profesor tenga disponibilidad ese día y turno y que dicte esa materia, y que no exista un choque con otro horario del mismo profesor, aula o sección en ese día y hora. Si algo falla, el horario no se guarda y se muestra el motivo exacto. El turno se calcula solo según la hora de inicio: matutino antes de las 12:00, vespertino de 12:00 a 6:00 pm, nocturno desde las 6:00 pm. En el formulario, si la hora de fin no es posterior a la de inicio o la matrícula supera la capacidad, el navegador avisa antes de enviar. Desde la lista puede ver el horario por profesor, imprimirlo o exportarlo a Excel.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="ayudaH7">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ayudaC7" aria-expanded="false" aria-controls="ayudaC7">Usuarios, roles y seguridad</button>
            </h2>
            <div id="ayudaC7" class="accordion-collapse collapse" role="region" aria-labelledby="ayudaH7" data-bs-parent="#ayudaAccordion">
                <div class="accordion-body">
                    Solo hay dos roles. El Administrador puede crear, editar y eliminar en todos los módulos; cualquier otro rol solo consulta. Las acciones restringidas a un usuario sin permiso muestran un error 403. La contraseña debe tener mínimo 16 caracteres. Tras 3 intentos fallidos de inicio de sesión la cuenta se bloquea y solo un administrador puede desbloquearla. El segundo factor (2FA) es opcional y, si está activo, también protege el cambio de contraseña, el cambio de respuestas de seguridad y su propia desactivación. La sesión se cierra sola tras 10 minutos sin actividad. El respaldo de la base de datos se hace desde phpMyAdmin (exportar), no desde el sistema.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="ayudaH8">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ayudaC8" aria-expanded="false" aria-controls="ayudaC8">Mensajes del sistema</button>
            </h2>
            <div id="ayudaC8" class="accordion-collapse collapse" role="region" aria-labelledby="ayudaH8" data-bs-parent="#ayudaAccordion">
                <div class="accordion-body">
                    Los mensajes verdes confirman que una acción se guardó. Los mensajes rojos indican un error o un bloqueo y siempre dicen el motivo: choque con otro horario, profesor sin disponibilidad, materia que el docente no dicta, capacidad excedida o permiso denegado. Al pasar el mouse sobre los campos con formato especial aparece una pista con el formato esperado. Si ve el aviso de sesión expirada, solo vuelva a iniciar sesión: no se perdió ningún dato guardado.
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="<?php echo $baseUrl; ?>dashboard" class="btn btn-primary"><i class="bi bi-grid-1x2-fill me-2"></i> Volver al Panel Principal</a>
    </div>
</div>
