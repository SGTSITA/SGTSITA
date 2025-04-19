<div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333;">
    <div style="background-color: #f1f5f9; padding: 20px; border-radius: 8px; box-shadow: 0 0 8px rgba(0,0,0,0.05);">
         <!-- Campo para escribir correo -->
         <div style="margin-bottom: 20px;">
            <label for="correoDestino" style="font-weight: 600; color: #1e3a8a;">📧 Correo destinatario:</label>
            <input type="email" id="correoDestino" placeholder="ejemplo@correo.com" style="
                width: 100%;
                padding: 10px 12px;
                border: 1px solid #cbd5e1;
                border-radius: 5px;
                background: #fff;
                color: #333;
            ">
        </div>
        

        <div style="margin-bottom: 15px;">
            <label style="font-weight: 600; color: #1e3a8a;">📝 Contenedor:</label>
            <div id="mensajeText" style="background: #fff; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 5px;">
                {{ $mensaje ?? '' }}
            </div>
        </div>

        <div style="margin-bottom: 10px;">
            <label for="linkMail" style="font-weight: 600; color: #1e3a8a;">🔗 Enlace</label>
            <div id="linkMail" style="background: #fff; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 5px;">
                {{ $enlace ?? '' }}
            </div>
            
        </div>
         <!-- Botón Enviar -->
         <div style="text-align: right;">
                           <!-- Si necesitas campos ocultos, agrégalos aquí -->
                <button onclick="enviarMailCoordenadas()" type="submit" style="
                    background-color: #2563eb;
                    color: white;
                    padding: 10px 20px;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    font-weight: 600;
                ">
                    ✉️ Enviar
                </button>
           
        </div>
    </div>
</div>
