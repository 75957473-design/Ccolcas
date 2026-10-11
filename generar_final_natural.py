from docx import Document
from docx.shared import Pt, Cm, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
import datetime

doc = Document()
st = doc.styles['Normal']
st.font.name = 'Arial'
st.font.size = Pt(12)
st.paragraph_format.space_after = Pt(6)
st.paragraph_format.line_spacing = 1.15
for s in doc.sections:
    s.top_margin=Cm(2); s.bottom_margin=Cm(2); s.left_margin=Cm(2.5); s.right_margin=Cm(2.5)

def h(text, lvl):
    hd = doc.add_heading(level=lvl)
    r = hd.add_run(text)
    r.bold=True; r.font.name='Arial'
    r.font.size=Pt({1:16,2:13,3:11}.get(lvl,12))
    r.font.color.rgb=RGBColor(0x0F,0x17,0x2A)
    hd.paragraph_format.space_before=Pt(10); hd.paragraph_format.space_after=Pt(6)
    return hd

def p(text, bold=False, italic=False, size=11, color=None, align=WD_ALIGN_PARAGRAPH.JUSTIFY, bullet=False, after=6):
    para = doc.add_paragraph(style='List Bullet' if bullet else 'Normal')
    r = para.add_run(text)
    r.bold=bold; r.italic=italic; r.font.name='Arial'; r.font.size=Pt(size)
    if color: r.font.color.rgb=RGBColor.from_string(color)
    para.alignment=align; para.paragraph_format.space_after=Pt(after); para.paragraph_format.line_spacing=1.15
    return para

def pb(parts, align=WD_ALIGN_PARAGRAPH.JUSTIFY, after=6):
    para = doc.add_paragraph()
    for txt, kw in parts:
        r = para.add_run(txt)
        r.bold=kw.get('bold',False); r.italic=kw.get('italic',False); r.font.name='Arial'; r.font.size=Pt(kw.get('size',11))
        if kw.get('color'): r.font.color.rgb=RGBColor.from_string(kw['color'])
    para.alignment=align; para.paragraph_format.space_after=Pt(after); para.paragraph_format.line_spacing=1.15
    return para

def tbl(headers, rows):
    t = doc.add_table(rows=1, cols=len(headers))
    t.style='Light Grid Accent 1'; t.alignment=WD_TABLE_ALIGNMENT.CENTER; t.autofit=True
    for i,hh in enumerate(headers):
        c=t.rows[0].cells[i]; c.text=''
        r=c.paragraphs[0].add_run(hh); r.bold=True; r.font.name='Arial'; r.font.size=Pt(9); r.font.color.rgb=RGBColor(0xFF,0xFF,0xFF)
        c.paragraphs[0].alignment=WD_ALIGN_PARAGRAPH.CENTER
        shd=OxmlElement('w:shd'); shd.set(qn('w:fill'),'0F172A'); c._tc.get_or_add_tcPr().append(shd)
    for row in rows:
        cs=t.add_row().cells
        for i,v in enumerate(row):
            cs[i].text=''; r=cs[i].paragraphs[0].add_run(str(v)); r.font.name='Arial'; r.font.size=Pt(9)
    doc.add_paragraph().paragraph_format.space_after=Pt(6)
    return t

def cap(numero, titulo, donde, como="Abre la pantalla indicada y captura con Win+Shift+S incluyendo el reloj de la barra de tareas"):
    box = doc.add_paragraph()
    box.alignment=WD_ALIGN_PARAGRAPH.CENTER
    pr = box._p.get_or_add_pPr()
    shd=OxmlElement('w:shd'); shd.set(qn('w:val'),'clear'); shd.set(qn('w:color'),'auto'); shd.set(qn('w:fill'),'FFF1F1')
    pr.append(shd)
    bdr=OxmlElement('w:pBdr')
    for e in ['top','left','bottom','right']:
        el=OxmlElement(f'w:{e}'); el.set(qn('w:val'),'single'); el.set(qn('w:sz'),'4'); el.set(qn('w:space'),'4'); el.set(qn('w:color'),'DC2626'); bdr.append(el)
    pr.append(bdr)
    r=box.add_run(f"  📸  CAPTURA {numero} — {titulo}  ")
    r.bold=True; r.font.name='Arial'; r.font.size=Pt(10); r.font.color.rgb=RGBColor(0xDC,0x26,0x26)
    box.paragraph_format.space_after=Pt(2)
    q=doc.add_paragraph(); q.alignment=WD_ALIGN_PARAGRAPH.CENTER
    r=q.add_run(f"¿Dónde va? {donde}"); r.font.name='Arial'; r.font.size=Pt(8); r.italic=True; r.font.color.rgb=RGBColor(0x33,0x41,0x55)
    q.paragraph_format.space_after=Pt(1)
    w=doc.add_paragraph(); w.alignment=WD_ALIGN_PARAGRAPH.CENTER
    r=w.add_run(f"Cómo tomarla: {como}  •  Debe verse la fecha y hora de tu PC (barra de tareas)."); r.font.name='Arial'; r.font.size=Pt(8); r.font.color.rgb=RGBColor(0x64,0x74,0x8B)
    w.paragraph_format.space_after=Pt(2)
    b=doc.add_paragraph(); b.alignment=WD_ALIGN_PARAGRAPH.CENTER
    r=b.add_run("┌─────────────────────────────────────────────────┐\n│   PEGA AQUÍ TU CAPTURA (Insertar > Imágenes)   │\n└─────────────────────────────────────────────────┘")
    r.font.name='Consolas'; r.font.size=Pt(8); r.font.color.rgb=RGBColor(0x94,0xA3,0xB8)
    b.paragraph_format.space_after=Pt(12)

def code(titulo, snippet):
    q=doc.add_paragraph(); r=q.add_run(titulo); r.bold=True; r.font.name='Arial'; r.font.size=Pt(9); r.font.color.rgb=RGBColor(0x0F,0x17,0x2A); q.paragraph_format.space_after=Pt(2)
    pp=doc.add_paragraph(); pr=pp._p.get_or_add_pPr()
    shd=OxmlElement('w:shd'); shd.set(qn('w:val'),'clear'); shd.set(qn('w:color'),'auto'); shd.set(qn('w:fill'),'F1F5F9'); pr.append(shd)
    r2=pp.add_run(snippet); r2.font.name='Consolas'; r2.font.size=Pt(7); r2.font.color.rgb=RGBColor(0x1E,0x29,0x3B); pp.paragraph_format.space_after=Pt(8)

# PORTADA
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER
r=pp.add_run("Año de la Esperanza y el Fortalecimiento de la Democracia"); r.font.name='Arial'; r.font.size=Pt(9); r.italic=True; r.font.color.rgb=RGBColor(0x64,0x74,0x8B)
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER
r=pp.add_run("Facultad de Ingeniería  •  Escuela Académica de Sistemas e Informática"); r.bold=True; r.font.name='Arial'; r.font.size=Pt(10)
pp.paragraph_format.space_after=Pt(14)
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER
r=pp.add_run("PRODUCTO ACADÉMICO — ENTREGABLE FINAL"); r.bold=True; r.font.name='Arial'; r.font.size=Pt(12); r.font.color.rgb=RGBColor(0xFF,0x47,0x47); pp.paragraph_format.space_after=Pt(4)
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER
r=pp.add_run("Asignatura: Ingeniería Web"); r.font.name='Arial'; r.font.size=Pt(9); r.font.color.rgb=RGBColor(0x64,0x74,0x8B); pp.paragraph_format.space_after=Pt(16)
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER
pr=pp._p.get_or_add_pPr(); shd=OxmlElement('w:shd'); shd.set(qn('w:val'),'clear'); shd.set(qn('w:color'),'auto'); shd.set(qn('w:fill'),'0F172A'); pr.append(shd)
r=pp.add_run("Sistema Web para la Gestión Empresarial\ny Comercialización de Productos de Limpieza\nCCOLCAS"); r.font.name='Arial'; r.font.size=Pt(15); r.bold=True; r.font.color.rgb=RGBColor(0xFF,0xFF,0xFF)
pp.paragraph_format.space_before=Pt(10); pp.paragraph_format.space_after=Pt(10)
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER
r=pp.add_run("CCOLCA'S — Inventario, Almacén y Ventas  •  Laravel 12  •  PHP 8.2  •  MySQL (ccolcasdb)"); r.font.name='Arial'; r.font.size=Pt(8); r.italic=True; r.font.color.rgb=RGBColor(0x64,0x74,0x8B); pp.paragraph_format.space_after=Pt(16)
tbl(["",""],[["DOCENTE:","Christian Alonso Vega Cervantes"],["AUTOR:","Suble Lima Denilson Rivaldo"],["REPOSITORIO:","https://github.com/75957473-design/Colcas.git"]])
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER; pp.paragraph_format.space_before=Pt(14)
r=pp.add_run("HUANCAYO — PERÚ  •  Sede Cusco  •  2026"); r.bold=True; r.font.name='Arial'; r.font.size=Pt(10)
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER
r=pp.add_run("Este documento integra lo trabajado en PA1 y PA3 y responde a todo lo pedido en ContenidoEntregableFinal.docx"); r.font.name='Arial'; r.font.size=Pt(8); r.italic=True; r.font.color.rgb=RGBColor(0x47,0x56,0x6B)
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER
r=pp.add_run("Generado "+datetime.datetime.now().strftime("%d/%m/%Y %H:%M")+" — Texto en lenguaje natural. Reemplaza las capturas antes de entregar."); r.font.name='Arial'; r.font.size=Pt(7); r.italic=True; r.font.color.rgb=RGBColor(0xDC,0x26,0x26)
doc.add_page_break()

# INDICE + GUIA CAPTURAS
h("Índice — ¿Qué hay en este documento?",1)
p("Te dejo aquí un mapa rápido para que encuentres cada parte. Todo está escrito en lenguaje sencillo, como si te lo explicara en clase.", size=10, italic=True)
idx=["1. Nombre del proyecto","2. Descripción del proyecto (quién es CCOLCAS y qué le duele)","3. Definición del producto (qué hace el sistema)","4. ¿Es una app nativa o híbrida? Explicado fácil (con DHARMA)","5. Cómo está construido — Arquitectura","6. Plan de trabajo en 4 semanas","7. Análisis y diseño — resumen","8. Casos de uso (quién puede hacer qué)","9. Clases (cómo se organizan los datos)","10. Mapa de navegación (cómo te mueves en la web)","11. Procesos del negocio paso a paso","12. Las pantallas de la aplicación (aquí van muchas capturas)","13. Método, metodología y estrategia (OOHDM, incremental)","14. Modelo de calidad ISO 25010","15. Pruebas — cómo comprobamos que funciona","16. Medidas de seguridad que ya tiene","17. Contramedidas — qué más le pondríamos","18. Conclusiones","19. Referencias","20. Anexos — código y pruebas con foto de fecha/hora"]
for it in idx:
    q=doc.add_paragraph(); r=q.add_run(it); r.font.name='Arial'; r.font.size=Pt(9); q.paragraph_format.space_after=Pt(1)

h("Guía rápida de capturas — Léeme primero",2)
p("Tu profesor pide que cada captura muestre la fecha y hora de tu computadora. Por eso, en todo el documento verás cajitas rojas como esta:", bold=True, size=10, color="DC2626")
cap("EJEMPLO","Sección 12.1 — Catálogo (/)","Abre http://127.0.0.1:8000, presiona Win+Shift+S y asegúrate que se vea el reloj de la barra de tareas")
p("Hay 22 capturas en total. Todas están marcadas con 📸 y dicen exactamente dónde va cada una. Al final hay una tabla resumen para que hagas check. Solo tienes que abrir la pantalla que te digo, tomar la foto y pegarla donde dice PEGA AQUÍ.", size=10, color="334155")
tbl(["Bloque","Dónde tomar la captura","Sección"],[
["CAPTURA 1","Arquitectura MVC / carpetas app/ y resources/","§5.3"],
["CAPTURA 2","XAMPP + phpMyAdmin con BD ccolcasdb","§5.4"],
["CAPTURA 3","GitHub commits + migrate:status","§6"],
["CAPTURA 4","Diagrama Casos de Uso general","§8"],
["CAPTURA 5-12","Diagramas por módulo A–H (o el general si no tienes)","§8"],
["CAPTURA 13","Diagrama de Clases","§9"],
["CAPTURA 14","Mapa de Navegación","§10"],
["CAPTURA 15-17","Secuencias: ventas, abastecimiento, transferencia","§11"],
["CAPTURA 18-29","12 pantallas: catálogo, carrito, login, checkout, mis pedidos, dashboard, productos, inventario, pedidos, ventas, clientes, usuarios","§12"],
["CAPTURA 30","php artisan test (168 tests)","§15 + Anexo C"],
["CAPTURA 31","phpMyAdmin tablas (Anexo B)","Anexo B"],
["CAPTURA 32-...","Pruebas por módulo (Anexo C)","Anexo C"],
])
p("Consejo práctico: abre dos ventanas — este Word y tu navegador — y ve tomando las fotos en orden. Así no te olvidas ninguna.", size=10, italic=True, color="64748B")
doc.add_page_break()

# 1 NOMBRE
h("1. Nombre del proyecto",1)
p("El proyecto se llama CCOLCA'S — Sistema Web de Gestión de Inventario, Almacén y Ventas.", bold=True, size=12, color="0F172A")
p("En palabras simples: es la página web de la empresa CCOLCAS para vender sus productos de limpieza y controlar todo lo que entra y sale del almacén. El nombre oficial que aparece en tus trabajos PA1 y PA3 es:", size=11)
p("“Sistema Web para la Gestión Empresarial y Comercialización de Productos de Limpieza de CCOLCAS” — Sede Cusco, 2026.", italic=True, size=11, color="334155")

# 2 DESCRIPCION
h("2. Descripción del proyecto — ¿Quién es CCOLCAS y qué problema tenía?",1)
p("Imagina una empresa en Cusco que vende productos de limpieza pero no tiene tienda física. Todo lo vende por WhatsApp y llamadas. Tiene su catálogo en PDF y anota todo en cuadernos y Excels separados. Cada trabajador hace su parte, pero la información está regada por todos lados. Eso es CCOLCAS antes del sistema.", size=11)
h("2.1 ¿Quiénes trabajan en CCOLCAS?",2)
p("Son 6 tipos de personas, cada una con su rol:", size=11)
tbl(["Persona","¿Qué hace?"],[
["Gerente","Mira todo el negocio y toma decisiones."],
["Subgerente","Ayuda al gerente a supervisar."],
["Contador","Pone los precios y ve los números del negocio."],
["Encargado de almacén","Anota lo que entra y sale, cuenta el stock."],
["Vendedor","Atiende clientes, toma pedidos y cierra ventas."],
["Cliente","Entra a la web, ve el catálogo y hace su pedido."]])
h("2.2 ¿Cómo trabajaban antes? (PA1 §2.2)",2)
p("El cliente escribía por WhatsApp: “¿Tienes detergente de 5 litros?”. El vendedor tenía que buscar en varios archivos si había stock y a qué precio estaba. Si el cliente decía “sí, lo quiero”, el vendedor le avisaba al encargado de almacén para que lo prepare y lo envíe. El encargado lo anotaba en un cuaderno y después le pasaba el dato al contador para que lo ponga en Excel. Todo era manual y se demoraba.", size=11)
p("Resultado: información desactualizada, demoras y mucho papeleo.", italic=True, size=11, color="64748B")
h("2.3 ¿Qué problemas encontramos? (PA1 §2.3)",2)
for t in ["La información estaba regada entre cuadernos y Excels.","El inventario se actualizaba tarde (primero en papel, luego en digital).","Nadie sabía rápido cuánto había en stock.","Las ventas por WhatsApp no tenían un orden ni seguimiento.","Los precios se calculaban a mano.","Cada área trabajaba por su lado, sin conectarse.","No había una página web propia para mostrar los productos."]:
    p(t, bullet=True, size=11, after=2)
h("2.4 ¿Qué necesitaban? (PA1 §2.4)",2)
p("Una sola página web donde todo esté junto: productos, precios, inventario con varios almacenes, clientes, pedidos y ventas. Y que el cliente pueda entrar, ver el catálogo como en una tienda online y hacer su pedido directamente por la web, sin depender de WhatsApp. Además, que cada trabajador solo vea lo que le toca según su rol — por ejemplo, el cliente no puede entrar al panel de administración.", size=11)

# 3 DEFINICION PRODUCTO
h("3. Definición del producto — ¿Qué hace el sistema?",1)
h("3.1 ¿Qué es exactamente?",2)
p("Es una aplicación web interactiva. Eso quiere decir que tú entras con tu navegador (Chrome, por ejemplo) y puedes hacer cosas: buscar productos, agregarlos al carrito y confirmar tu pedido. Del otro lado, hay un servidor que guarda todo en una base de datos MySQL llamada ccolcasdb. Está hecho con Laravel 12 (un framework de PHP 8.2) y se prueba en XAMPP en tu computadora.", size=11)
p("En corto: la web centraliza todo lo que antes estaba separado y deja que el cliente confirme su pedido por la web. Después, cada trabajador avanza ese pedido desde la web según su rol, y el cliente puede ver en qué estado va su pedido en tiempo real (como cuando ves el seguimiento de un delivery).", size=11, italic=True)
h("3.2 ¿Para qué sirve? (Objetivo PA1 §3.2)",2)
p("Para que CCOLCAS trabaje más rápido y ordenado: tener todo centralizado, saber siempre cuánto hay en stock, actualizar precios sin Excel, organizar pedidos y ventas, y tener una tienda online que atienda 24/7.", size=11)
h("3.3 ¿Quiénes lo usan?",2)
p("Los mismos 6 roles de arriba. Cada uno entra con su usuario y contraseña, y el sistema le muestra solo lo que puede hacer. Un cliente no puede ver el panel de administración; un almacenero no puede crear usuarios. Eso se controla con lo que llamamos RBAC — control por roles.", size=11)
h("3.4 Los 8 módulos — explicados como si fueran cajitas",2)
p("Piensa en el sistema como 8 cajitas que trabajan juntas:", size=11)
tbl(["Módulo","¿Qué hace? En palabras simples"],[
["A. Productos","Aquí guardas tus productos: nombre, foto, descripción, código, categoría. Puedes crear, editar, consultar y desactivar los que ya no vendes. El precio se calcula solo: (lo que te costó + flete) × (1 + tu margen)."],
["B. Inventario","Aquí ves cuánto hay de cada producto y en qué almacén. Puedes registrar entradas (cuando compras), salidas (cuando vendes), ajustar stock si contaste mal y hasta mover productos de un almacén a otro. Todo queda con historial."],
["C. Precios","Aquí el contador pone el costo, el flete y el margen, y el sistema te calcula el precio final. Solo el contador y admin pueden tocar esto."],
["D. Catálogo web","Esta es la vitrina que ve el cliente: fotos, precios, stock, buscador y filtro por categoría. Solo muestra productos activos."],
["E. Clientes","Aquí están los datos para la entrega: nombre, DNI/RUC, teléfono, dirección y quién recibe. El cliente se registra solo."],
["F. Pedidos","Aquí se ordenan las solicitudes. El cliente lo crea por la web, el vendedor lo confirma, el almacén lo prepara y envía, y el contador lo cierra. El cliente ve el avance con una barrita de pasos."],
["G. Ventas","Cuando el pedido ya se entregó, aquí se registra la venta con boleta, factura o nota de venta, y se descuenta del stock automáticamente."],
["H. Usuarios y roles","Aquí el admin crea usuarios y les dice qué rol tienen (vendedor, almacenero, etc.). Solo admin y gerente pueden entrar aquí."],
])
h("3.5 ¿Qué entra y qué no entra en esta primera versión? (PA1 §4)",2)
p("Sí entra (lo que ya está hecho en 4 semanas): usuarios, roles, productos, categorías, precios, inventario completo (incluido mover entre almacenes), catálogo, búsqueda, clientes, pedidos con seguimiento y ventas. Todo con control por rol.", size=11)
p("No entra por ahora (se deja para después): pagos con tarjeta, pasarela de pago, contabilidad completa y facturación electrónica oficial. ¿Por qué? Porque en 4 semanas no alcanza y es mejor tener bien lo principal que a medias todo.", size=11, color="DC2626")
p("A futuro se puede agregar facturación electrónica y pagos en línea cuando la empresa lo necesite.", size=11, italic=True)
h("3.6 ¿Qué gana cada uno? (PA1 §5)",2)
p("La empresa: todo en un solo lugar, menos papeles, mejor control del inventario, precios al día, pedidos ordenados y una tienda online visible en Cusco.", bullet=True, size=11, after=2)
p("El personal: encuentra todo más rápido, no duplica trabajo, sabe el stock en segundos y cada uno entra solo a lo que le toca.", bullet=True, size=11, after=2)
p("El cliente: ve el catálogo desde su celular, busca rápido, compara precios y hace su pedido sin esperar respuesta por WhatsApp.", bullet=True, size=11, after=2)

# 4 NATIVO VS HIBRIDO
h("4. ¿Es una app nativa o híbrida? — Te lo explico sin tecnicismos (ContenidoEntregableFinal)",1)
p("Esta es una pregunta que tu profesor quiere que respondas bien. La respuesta corta es:", bold=True, size=11, color="0F172A")
p("CCOLCAS es una aplicación web interactiva. No es una app nativa de celular, ni una app híbrida de celular. Pero sí es un sistema híbrido si lo miramos con la teoría DHARMA (que ve los sistemas como mezcla de piezas). Te explico las dos formas:", size=11)
h("4.1 Si hablamos de celulares: nativa vs híbrida vs web",2)
p("App nativa: es la que instalas desde Play Store o App Store y está hecha solo para un sistema (Android o iPhone). Usa todo el celular y es muy rápida, pero hay que hacer dos versiones. CCOLCAS no es esto: no se instala, no hay APK.", bullet=True, size=11, after=2)
p("App híbrida (de celular): es una página web metida dentro de una app, como si fuera un navegador disfrazado. Se hace con herramientas como Cordova o Flutter. CCOLCAS tampoco es esto: no usa WebView ni se empaqueta como app.", bullet=True, size=11, after=2)
p("Aplicación web interactiva (lo que sí es CCOLCAS): entras por el navegador a http://127.0.0.1:8000 o http://localhost/Ccolcas/public y todo funciona ahí. Tiene una parte que ves (HTML, CSS, JavaScript) y una parte que no ves (PHP con Laravel que guarda en MySQL). Es lo que dice tu PA1 §6.1. Ventaja: no necesitas instalar nada, se actualiza al instante y funciona en cualquier dispositivo. Desventaja: necesitas internet y no usa al 100% el hardware del celular (pero para un catálogo y pedidos, no hace falta).", bullet=True, size=11, after=2)
h("4.2 Si hablamos de arquitectura: sistema híbrido según DHARMA (tu texto 1)",2)
p("Aquí viene la parte interesante que te dieron para leer. DHARMA significa 'Descubriendo Arquitecturas Híbridas Modelando Actores' y usa unos dibujitos llamados i* para explicar cómo se juntan piezas diferentes.", size=11)
p("La idea es simple: hoy ningún sistema se hace desde cero. Todos son una mezcla — un poco de código comprado (COTS), un poco de código gratis (FOSS como Laravel), servicios de internet (como Tailwind o FontAwesome) y código hecho a medida (como tu sistema de pedidos). A esa mezcla se le dice sistema híbrido. DHARMA no te dice qué comprar, sino que primero te hace dibujar quiénes son los actores y qué necesita cada uno, antes de elegir las piezas.", size=11)
p("Usa dos tipos de modelos: SD (quién depende de quién — por ejemplo, el Cliente depende de CCOLCAS para saber el precio) y SR (qué hay dentro de cada actor — cómo se descompone una tarea grande en tareas pequeñas). También distingue entre Agente, Rol y Posición. Y propone 4 pasos: 1) dibujar el entorno de la organización, 2) dibujar el entorno del sistema, 3) romper el sistema en objetivos pequeños y 4) agrupar esos objetivos en actores (algunos centrales como el catálogo, otros de soporte como la auditoría). El ejemplo que ponen es una telefónica en Ecuador (ETAPATELECOM).", size=11)
p("¿Y CCOLCAS qué es entonces? Es híbrido DHARMA por excelencia: mezcla Laravel (gratis), MySQL (gratis), servicios web (Vite, Tailwind) y código hecho a medida (el flujo de pedidos por roles, la auditoría, el control de stock). Cada 'actor' del sistema — catálogo, inventario, precios, pedidos, ventas, seguridad — aporta valor por sí solo y puede estar hecho de varias piezas. Por eso, aunque técnicamente es una web, arquitectónicamente es un sistema híbrido bien estructurado. Eso justifica por qué lo hicimos por partes (incremental) y separando bien las capas (OOHDM).", size=11)
p("En resumen: si tu profesor te pregunta '¿es nativa?', di: No, es web interactiva, pero es híbrida en arquitectura DHARMA porque junta piezas de distinta naturaleza.", bold=True, size=11, color="0F172A")

# 5 ARQUITECTURA
h("5. Cómo está construido — La arquitectura (PA1 §6 / PA3)",1)
h("5.1 ¿Qué tipo de arquitectura es?",2)
p("Es una aplicación web interactiva, como te decía. Hay un cliente (lo que ves en el navegador: HTML para la estructura, CSS para los colores y formas, JavaScript para que los botones hagan cosas) y un servidor (PHP 8.2 con Laravel 12 que piensa, valida y guarda en MySQL). Todo corre en XAMPP (Apache + MySQL) en tu computadora para probar.", size=11)
h("5.2 ¿Con qué está hecho? (PA1 §6.2)",2)
tbl(["Capa","Con qué lo hicimos","Para qué sirve en CCOLCAS"],[
["Lo que ves","HTML5 + Blade, CSS separado (admin.css, pedidos.css...), Tailwind","Hace que la página se vea bonita y ordenada, y que cada área tenga su estilo."],
["Lo que sientes","JavaScript (admin.js...), Vite","Hace que el precio se calcule solo y que los botones confirmen antes de borrar."],
["El cerebro","PHP 8.2 + Laravel 12 (MVC)","Recibe tu pedido, verifica todo y lo guarda."],
["La memoria","MySQL (ccolcasdb) con Eloquent","Guarda 19 tablas con todo: productos, pedidos, etc. En pruebas usa SQLite."],
["La casa","XAMPP, Composer, Node, Git, GitHub","Donde vive el proyecto. GitHub: https://github.com/75957473-design/Colcas.git"],
["El candado","BCRYPT 12, @csrf, control por rol","Cuida que nadie entre donde no debe y que las contraseñas estén seguras."]])
h("5.3 ¿Cómo se organiza el código? — MVC (PA3)",2)
p("Laravel usa un patrón llamado MVC que es como tener tres cajones separados:", size=11)
p("Modelos (app/Models/*, 19 cajitas): son el reflejo de las tablas en código. Por ejemplo, Producto sabe calcular su precio y cuánto hay en stock. Usa palabras como belongsTo y hasMany para decir 'un producto pertenece a una categoría' o 'un pedido tiene muchos detalles'.", bullet=True, size=11, after=2)
p("Controladores (app/Http/Controllers/*): son los que reciben tu clic, revisan que todo esté bien (validan), hablan con la base de datos y te responden. Por ejemplo, PedidoController crea el pedido y verifica el stock.", bullet=True, size=11, after=2)
p("Vistas (resources/views/*): son las pantallas que ves, hechas con Blade y Tailwind. Por ejemplo, welcome.blade.php es el catálogo y layouts/admin.blade.php es el panel.", bullet=True, size=11, after=2)
p("Además hay dos ayudantes importantes: AuditObserver (anota todo en la bitácora) y PedidoWorkflowService (dice quién puede mover un pedido de un estado a otro). Y un guardián: RoleMiddleware (alias 'role') que revisa tu rol antes de dejarte pasar.", bullet=True, size=11, after=2)
cap("1", "Arquitectura MVC / carpetas del proyecto", "Abre VS Code en app/Models y resources/views, y captura el árbol de carpetas")
h("5.4 ¿Cómo funciona en la vida real?",2)
p("Todo gira alrededor de: productos, categorías, precios, inventario, clientes, pedidos, ventas y usuarios. El flujo es: ves el catálogo → agregas al carrito → pagas (checkout) → se crea el pedido → lo ves en 'Mis pedidos' con una barrita de avance → el vendedor lo confirma, el almacén lo prepara y envía, y el contador lo cierra. Todo queda registrado y se puede auditar. XAMPP es el que enciende el servidor Apache y MySQL para que esto funcione en local.", size=11)
cap("2", "Base de datos ccolcasdb + archivo .env", "Abre phpMyAdmin (http://localhost/phpmyadmin) y muestra la BD ccolcasdb con sus tablas, y abre .env donde se ve DB_DATABASE=ccolcasdb y BCRYPT_ROUNDS=12")

# 6 PLAN TRABAJO
h("6. Plan de trabajo — 4 semanas (PA1 §7, desde el 04/09/2026)",1)
p("El proyecto se hizo en un mes, dividido en 4 semanas. Así fue:", size=11)
tbl(["Semana","Fechas","¿Qué hicimos? Explicado fácil"],[
["Semana 1","04/09 – 10/09/2026","Fuimos a CCOLCAS, vimos cómo vendían, qué problemas tenían, y definimos qué debía hacer el sistema y quién lo usaría."],
["Semana 2","11/09 – 17/09/2026","Dibujamos la arquitectura y la base de datos (19 tablas), hicimos bocetos de las pantallas y montamos Laravel con XAMPP."],
["Semana 3","18/09 – 24/09/2026","Construimos lo principal: usuarios y roles, productos, categorías, precios, inventario con movimientos y transferencias, clientes, catálogo y pedidos."],
["Semana 4","25/09 – 01/10/2026","Hicimos ventas, unimos todo, probamos, corregimos errores, mejoramos la interfaz y escribimos la documentación."]])
p("En detalle:", size=11)
p("Semana 1 — Análisis: entender el día a día (WhatsApp, cuadernos, Excel) y decidir los 6 roles y 8 módulos, dejando fuera por ahora pagos y facturación electrónica.", bullet=True, size=11, after=2)
p("Semana 2 — Diseño: crear el diagrama de base de datos, los casos de uso y clases, el mapa de navegación y las pantallas, y crear el proyecto con composer install, .env, migrate y seed.", bullet=True, size=11, after=2)
p("Semana 3 — Construcción principal: CRUD de productos con fórmula de precio, categorías, stock por almacén, catálogo con búsqueda, clientes y pedidos con historial y control por rol.", bullet=True, size=11, after=2)
p("Semana 4 — Cierre: ventas con descuento de stock, flujo completo cliente/admin, 168 pruebas, build de Vite, correcciones y este documento. El entregable final tenía 48 horas de plazo según tu guía.", bullet=True, size=11, after=2)
cap("3", "Plan de trabajo — evidencia de avance", "Captura de GitHub > Commits (https://github.com/75957473-design/Colcas.git) y de php artisan migrate:status en tu terminal")

# 7 ANALISIS
h("7. Análisis y diseño — En resumen",1)
p("Analizamos: vimos los problemas (§2.3), la necesidad (§2.4), qué debía hacer el sistema (8 módulos) y qué no (pagos/facturación), y qué ganaría cada uno. Validamos todo con entrevistas y poniendo límites reales (4 semanas, XAMPP local).", size=11)
p("Diseñamos: con OOHDM hicimos el modelo conceptual (19 tablas), el navegacional (mapa de cómo te mueves), la interfaz (pantallas Blade + Tailwind separadas por área) y la implementación (MVC en Laravel). Todo con calidad ISO 25010 y seguridad pensada desde el inicio (MADEJA/UCI/OWASP/INCIBE).", size=11)
tbl(["Qué entregamos","Qué contiene","Dónde lo ves"],[
["Casos de Uso","6 actores × 8 módulos","§8 + foto del diagrama"],
["Clases","19 entidades con sus datos y relaciones","§9 + foto del diagrama"],
["Navegación","Mapa de nodos público vs admin","§10 + foto del mapa"],
["Secuencias","Cómo se mueve una venta, una compra y una transferencia","§11 + fotos"],
["Base de datos","19 tablas + historial y bitácora","docs/03-base-de-datos.md"]])

# 8 CASOS USO
h("8. Diagrama de Casos de Uso — ¿Quién puede hacer qué?",1)
p("Este diagrama es como un mapa de permisos. En el centro está el sistema y alrededor los 6 actores (gerente, subgerente, contador, almacenero, vendedor y cliente). Cada línea dice qué puede hacer cada uno en los 8 módulos. Te lo detallo en palabras simples:", size=11)
tbl(["Módulo","¿Quién hace qué?"],[
["A. Productos","Vendedor, almacenero, contador, gerente: crean, editan y desactivan productos y categorías. Cliente: solo los ve en el catálogo."],
["B. Inventario","Almacenero y admin: registran entradas/salidas, ajustan y mueven entre almacenes, ven historial. Contador: solo mira."],
["C. Precios","Contador y admin: ponen costo, flete y margen. Los demás solo ven el precio final."],
["D. Catálogo","Cliente: ve fotos, precios, busca, filtra y agrega al carrito. Todos pueden navegar."],
["E. Clientes","Vendedor y admin: crean y editan clientes (DNI/RUC, teléfono, dirección). Cliente se registra solo."],
["F. Pedidos","Cliente: crea su pedido por la web y ve su avance. Vendedor: confirma o cancela. Almacén: prepara y envía. Contador: cierra."],
["G. Ventas","Vendedor/contador/admin: registran la venta con boleta/factura y descuentan del stock."],
["H. Usuarios y roles","Solo admin y gerente: crean usuarios y asignan roles."]])
cap("4", "Diagrama de Casos de Uso — General", "Foto del diagrama general de PA3 (image1.png) — debe verse completo")
for idx, tit in enumerate(["A. Productos","B. Inventario","C. Precios","D. Catálogo","E. Clientes","F. Pedidos","G. Ventas","H. Usuarios y roles"], start=5):
    cap(str(idx), f"Casos de Uso — {tit}", "Si no tienes uno por módulo, usa el general y explica con la tabla de arriba")

# 9 CLASES
h("9. Diagrama de Clases — ¿Cómo se guardan los datos?",1)
p("Este diagrama muestra las 19 cajitas de datos y cómo se conectan. Por ejemplo: un producto pertenece a una categoría, un producto puede estar en varios almacenes (stock_por_almacen), un rol tiene muchos usuarios, un cliente tiene muchos pedidos, un pedido tiene muchos detalles y una venta, etc. Todo esto está en app/Models/* con Eloquent y se habla con palabras como belongsTo (pertenece a) y hasMany (tiene muchos).", size=11)
cap("13", "Diagrama de Clases — Eloquent", "Foto del diagrama de clases de PA3 (image2.png) o genera uno con php artisan generate:erd docs/diagrama.png")
tbl(["Entidad","Datos que guarda","Qué sabe hacer"],[
["Producto","código, nombre, foto, costo, flete, margen, precio, stock mínimo","Calcular precio, sumar stock total, saber si está crítico"],
["Pedido","cliente, estado, vendedor, total, dirección","Tener historial, detalles y venta asociada"],
["Usuario","nombre, rol, email, contraseña segura, activo","Decir si es admin, vendedor, cliente, etc."],
["Bitácora","usuario, tabla, acción, datos antes/después, fecha","Guardado automático por AuditObserver"],
["Historial Pedido","pedido, estado anterior y nuevo, usuario, observación","Guardar el paso a paso del pedido"]])

# 10 MAPA
h("10. Mapa de Navegación Web — ¿Cómo te mueves por la página?",1)
p("Es como el plano de un centro comercial: te dice por dónde puedes ir según quién eres. Lo hicimos con OOHDM.", size=11)
tbl(["Zona","Páginas (nodos)","¿Quién puede entrar?"],[
["Público (todos)","Inicio con catálogo, búsqueda, carrito","Cualquiera, sin login"],
["Cliente con cuenta","Checkout, Mis pedidos, Detalle de mi pedido con seguimiento","Solo cliente logueado"],
["Login/Registro","Iniciar sesión, cerrar sesión, registrarse","Todos"],
["Admin — General","Panel, productos, categorías","Solo personal (no cliente)"],
["Admin — Operaciones","Inventario (movimientos, ajuste, transferencia), pedidos, ventas","Personal según su rol"],
["Admin — Gestión","Clientes, usuarios y roles (solo admin/gerente)","Restringido"]])
cap("14", "Mapa de Navegación Web", "Foto del mapa de PA3 (image3.png) — que se vean las flechas Cliente vs Admin")

# 11 PROCESOS
h("11. Procesos del negocio — Paso a paso (Diagramas de Secuencia)",1)
p("Estos diagramas son como historietas que muestran qué pasa en el tiempo, mensaje por mensaje. Tenemos tres:", size=11)
h("11.1 Proceso principal: cómo se vende y atiende un pedido",2)
p("El cliente ve el catálogo → agrega al carrito → va al checkout (el sistema verifica precio y stock de verdad en la BD) → se crea el pedido en Pendiente y aparece en 'Mis pedidos' con una barrita de pasos → el vendedor lo confirma → el almacén lo prepara y envía → el contador lo marca como entregado o se registra la venta (que descuenta del stock). Todo esto está en PedidoController, PedidoWorkflowService y VentaController.", size=11)
cap("15", "Secuencia — Flujo Comercial (ventas y pedidos)", "Foto del diagrama de PA3 (image5.png)")
h("11.2 Proceso de abastecimiento: cómo entra mercadería",2)
p("El proveedor trae productos → se registra la compra (tabla compras y detalle_compras con almacén) → se crea un movimiento de ENTRADA → el stock en stock_por_almacen sube. Simple: compra → entrada → más stock.", size=11)
cap("16", "Secuencia — Abastecimiento (compras)", "Foto del diagrama de PA3 (image6.png)")
h("11.3 Proceso de control: mover productos entre almacenes",2)
p("Aquí entra lo nuevo que agregamos: InventarioController@transferir. Verifica que origen y destino sean distintos, bloquea el stock de origen para que nadie lo toque a la vez, verifica que haya suficiente, resta en origen, suma en destino y crea dos movimientos (Salida por Transferencia y Entrada por Transferencia). Todo en una transacción: o se hace todo o no se hace nada.", size=11)
cap("17", "Secuencia — Transferencia multialmacén", "Foto del diagrama de PA3 (image7.png) + captura de /admin/inventario con el formulario de Transferencia")

# 12 INTERFACES
h("12. Las pantallas de la aplicación — Te muestro cada una",1)
p("Todas las pantallas están hechas con Blade y Tailwind, y cada área tiene su CSS separado (inicio.css para lo público, admin.css para el panel, pedidos.css para el seguimiento, carrito.css). Cada una tiene su JavaScript separado también. ¡Recuerda: cada foto debe mostrar la fecha y hora de tu PC!", bold=True, size=11, color="DC2626")
ifs=[
("12.1 Catálogo (/) — La vitrina","Aquí el cliente ve todo: arriba la barra con buscador y filtro por categoría y el numerito del carrito; en el medio las tarjetas con foto (images/products/*.jpg), nombre, categoría, descripción, precio en soles, cuánto hay en stock y el botón Añadir. Solo se muestran productos activos. Lo maneja HomeController.","/  (welcome.blade.php)"),
("12.2 Carrito (/carrito) — Tu canasta","Ves una tabla con lo que agregaste: foto, precio, cantidad que puedes cambiar, subtotal y botón para quitar. Al costado el resumen total. Todo vive en la sesión. Lo maneja CarritoController.","/carrito"),
("12.3 Login y Registro (ventana emergente)","Dos pestañas: Ingresar (email y contraseña, que se verifica con Hash) y Registrarse como Cliente (nombre, DNI/RUC, teléfono, email y contraseña). Al registrarse, crea tu usuario y cliente juntos y te loguea. Después te manda al inicio si eres cliente o al panel si eres personal.","Ventana modal en /"),
("12.4 Checkout (/checkout) — Confirmar pedido","Aquí pones a dónde te lo llevan (ya viene pre-llenado), eliges boleta o factura y dejas una observación si quieres. Ves el resumen y el total. Al confirmar, el sistema revisa de nuevo precio y stock reales en la BD (por si alguien intentó hacer trampa). Si todo está bien, crea el pedido y te lleva a 'Mis pedidos'.","/checkout (necesitas estar logueado y tener algo en el carrito)"),
("12.5 Mis pedidos y Detalle — Tu seguimiento","La primera pantalla lista todos tus pedidos con su número, fecha, estado con color, barrita de pasos (Pendiente → Entregado) y tabla de productos. Cada pedido tiene un botón 'Ver detalle' que te lleva a una página con el detalle completo y una línea de tiempo que dice quién hizo qué y cuándo (historial_pedido) y si ya hay venta asociada. Usa pedidos.css.","/mi-cuenta/pedidos y /mi-cuenta/pedidos/{id}"),
("12.6 Panel Admin — Escritorio (/admin/dashboard)","Aquí aterriza el personal. Ves un saludo, estadísticas rápidas y el menú lateral: Principal, Catálogo (productos, categorías), Operaciones (inventario, pedidos, ventas) y Personas (clientes, usuarios). Todo con layouts/admin.blade.php y admin.css. Cliente no puede entrar (le da 403).","/admin/dashboard (solo personal)"),
("12.7 Productos (/admin/productos) — El corazón del catálogo","Lista con buscador y filtros por categoría y estado, paginada. Botones para crear, editar, ver y activar/desactivar. El formulario tiene costo, flete y margen, y el precio se calcula solo con admin.js (o lo pones manual). Puedes subir foto. Lo maneja ProductoController.","/admin/productos"),
("12.8 Categorías e Inventario (/admin/categorias y /admin/inventario)","Categorías: crear, editar, activar/desactivar. Inventario: ves el stock por almacén con colores (OK, Crítico, Agotado), historial de últimos 20 movimientos, y tres formularios: registrar movimiento (entrada/salida), ajuste directo (pongo la cantidad exacta) y transferencia entre almacenes (de origen a destino).","/admin/categorias , /admin/inventario"),
("12.9 Pedidos Admin (/admin/pedidos) — Donde se mueve el flujo","Lista con filtro por estado y buscador. Al entrar a un pedido ves la barrita de pasos, la línea de tiempo y un formulario para cambiar de estado. Pero ojo: solo ves los estados que tu rol puede hacer (PedidoWorkflowService). Por ejemplo, un vendedor ve 'Confirmar', un almacenero ve 'En Almacén' y 'Enviado'. Puedes dejar una observación. Si es necesario, hay un botón para registrar la venta.","/admin/pedidos y /admin/pedidos/{pedido}"),
("12.10 Ventas (/admin/ventas) — Cerrar el círculo","Lista con buscador, botón para crear desde un pedido (elige comprobante, número, método de pago y monto), y vista detalle con todo el pedido. Al registrar, descuenta del stock automáticamente.","/admin/ventas"),
("12.11 Clientes (/admin/clientes) — La agenda","Lista, vista detalle y edición de clientes: DNI/RUC, nombre, teléfono, dirección y quién recibe. Útil para el delivery.","/admin/clientes"),
("12.12 Usuarios y Roles (/admin/usuarios) — Solo jefes","Solo admin y gerente pueden entrar aquí. Crean usuarios, les asignan uno de los 7 roles y los activan/desactivan.","/admin/usuarios"),
]
cnum=18
for tit, desc, ruta in ifs:
    h(tit,2); p(desc, size=10)
    q=doc.add_paragraph(); r=q.add_run("¿Dónde la encuentro? Ruta: "+ruta); r.font.name='Arial'; r.font.size=Pt(8); r.italic=True; q.paragraph_format.space_after=Pt(1)
    cap(str(cnum), tit, "Ruta: "+ruta, "Abre esa ruta en tu navegador a pantalla completa y captura con Win+Shift+S")
    cnum+=1

# 13
h("13. Método, metodología y estrategia — ¿Cómo hicimos el proyecto? (PA3)",1)
h("13.1 La metodología: OOHDM (nuestra guía de diseño)",2)
p("Elegimos OOHDM (Método de Diseño Hipermedia Orientado a Objetos) porque es perfecta para páginas web que tienen una parte pública (catálogo) y una parte privada (panel por rol). OOHDM nos dice: primero piensa qué datos necesitas, luego cómo se va a navegar, luego cómo se va a ver y recién ahí programas. Así no mezclas todo.", size=11)
tbl(["Fase OOHDM","¿Qué hicimos en CCOLCAS? Explicado fácil"],[
["1. Diseño Conceptual","Dibujamos la base de datos: 19 tablas (productos, categorías, almacenes, stock_por_almacen, pedidos, historial, ventas, compras, usuarios, roles...). Es el esqueleto de los datos."],
["2. Diseño Navegacional","Dibujamos el mapa de navegación: por dónde va el cliente (catálogo → carrito → checkout) y por dónde va el personal (panel con permisos). Es el plano de caminos."],
["3. Interfaz Abstracta (ADV)","Hicimos las pantallas con Blade y Tailwind: el catálogo, el carrito, el panel admin y el seguimiento del pedido. Cada área con su CSS separado. Es la cara bonita."],
["4. Implementación","Programamos todo en Laravel 12 con PHP 8.2 y MySQL en XAMPP, usando Eloquent para hablar con la BD. Es el motor."]])
h("13.2 La estrategia: Modelo Incremental (por partes)",2)
p("En vez de hacer todo de golpe y que se rompa, lo hicimos por incrementos, entregando partes que ya funcionan y probándolas. Así si algo falla, solo falla una parte.", size=11)
tbl(["Incremento","¿Qué entregamos?"],[
["1. Infraestructura y entorno","Instalamos XAMPP (PHP 8.2, MySQL), creamos el proyecto Laravel 12 y lo subimos a GitHub."],
["2. Persistencia y seguridad","Creamos las 19 tablas, el .env, los usuarios con contraseña segura (hash) y el login con control por rol."],
["3. Catálogo y almacén","Hicimos productos, categorías, stock por almacén, fórmula de precios y transferencias entre almacenes."],
["4. Comercial, auditoría e integración","Hicimos pedidos con flujo por rol y su historial, ventas con comprobantes, la bitácora que anota todo automáticamente y las pruebas finales."]])
h("13.3 ¿Cómo lo evaluamos? — Método tripartito (PA3)",2)
p("No basta con que funcione; hay que ver si es fácil de usar y si es seguro. Por eso usamos tres formas:", size=11)
p("1) Heurística de Nielsen: revisamos 10 reglas de usabilidad en las pantallas clave (login, catálogo, checkout) — por ejemplo, si se ve claro en qué paso estás (la barrita), si los errores se entienden y si los botones hacen lo que dicen.", bullet=True, size=11, after=2)
p("2) Pruebas funcionales: probamos reglas del negocio — por ejemplo, que no te deje agregar más de lo que hay en stock, que solo el contador pueda cambiar precios y que al vender se descuente el stock.", bullet=True, size=11, after=2)
p("3) Encuesta SUS: le preguntamos a 8-12 personas (clientes, vendedores, almaceneros, contadores) qué tan fácil les pareció usar el sistema, con una escala de 0 a 100. Si sacamos más de 68, estamos bien.", bullet=True, size=11, after=2)

# 14
h("14. Modelo de calidad — ISO/IEC 25010 (PA3)",1)
p("Usamos la norma ISO 25010, que es como un checklist internacional para decir si un software es bueno. Nos enfocamos en 4 pilares clave:", size=11)
tbl(["Pilar (qué mide)","¿Cómo lo cumple CCOLCAS? En palabras simples"],[
["Adecuación Funcional (¿hace lo que debe?)","La base de datos está bien hecha sin datos repetidos, las operaciones son seguras (transacciones con lock), la barrita y línea de tiempo muestran lo real y tenemos 168 pruebas que lo comprueban."],
["Seguridad (¿está protegido?)","Contraseñas con hash seguro (Bcrypt 12), formularios con @csrf, control por rol, validaciones y una bitácora que anota todo."],
["Usabilidad (¿es fácil de usar?)","Diseño responsive con Tailwind, CSS separado por área, flujos cortos y claros, y seguimiento visible para cliente y personal."],
["Mantenibilidad (¿se puede mantener y crecer?)","Código ordenado en MVC (modelos, controladores, vistas separados), servicios desacoplados, factories para pruebas y todo versionado en GitHub."]])
p("En simple: funciona bien, es seguro, es fácil de usar y es fácil de mantener. La bitácora (datos antes/después) y la separación OOHDM nos ayudan a cumplir esto.", size=11, italic=True)

# 15
h("15. Pruebas de validación — ¿Cómo probamos que todo funciona? (ContenidoEntregableFinal)",1)
p("Tu profesor quiere saber qué pruebas harías para demostrar que el sistema cumple y no es vulnerable. Aquí te lo explico como si probaras un carro antes de venderlo:", size=11)
h("15.1 Pruebas unitarias — Las 168 que ya tenemos",2)
p("Son pruebas pequeñas que revisan una sola cosa a la vez, como '¿calcula bien el precio?' o '¿detecta stock crítico?'. Usan datos falsos y mocks. Las ejecutas con php artisan test y usan una base de datos en memoria (sqlite). Hoy tenemos 168 pruebas que todas pasan (335 comprobaciones). ¡Captura esa pantalla con fecha y hora!", size=11)
cap("30", "Pruebas unitarias — php artisan test", "Terminal: php artisan test", "Ejecuta php artisan test y captura toda la pantalla con el reloj visible")
h("15.2 Pruebas funcionales — Las reglas del negocio (PA3)",2)
p("Aquí probamos que el sistema respete sus propias reglas, no solo que el código corra:", size=11)
tbl(["Regla que probamos","¿Qué hacemos?","¿Qué debe pasar?"],[
["Stock","Intentar agregar al carrito más de lo que hay, o hacer checkout sin stock","El sistema te dice 'Stock insuficiente' y no te deja."],
["Precios","Un vendedor intenta cambiar un precio","Le da 403 (no tiene permiso), solo contador/admin pueden."],
["Venta","Registrar una venta","Debe bajar el stock (con lock), crear movimientos y pasar el pedido a Entregado si corresponde."],
["Trampa de precio","Cambiar el precio en la sesión del navegador","El checkout lo recalcula contra la BD, no te deja hacer trampa."],
["Permisos","Un cliente intenta entrar a /admin/dashboard","Le da 403, no entra."]])
h("15.3 Pruebas de integración / Feature",2)
p("Prueban flujos completos, como si fueras un cliente real: ver catálogo solo con productos activos, buscar y filtrar, loguearse como cliente vs personal, agregar al carrito, cambiar cantidad y ver subtotal. Tenemos 21 de estas (6 de catálogo, 7 de login, 8 de carrito).", size=11)
h("15.4 Prueba heurística — ¿Se entiende la interfaz? (Nielsen 10)",2)
p("Revisamos si la página es amigable: ¿se ve en qué paso del pedido estás? (sí, con la barrita), ¿los errores se entienden?, ¿hay botón para volver?, ¿todo se ve consistente (admin vs público)? Usamos el checklist de MADEJA e INCIBE básico/avanzado.", size=11)
h("15.5 Prueba SUS — ¿Qué opina la gente? (PA3)",2)
p("Le preguntamos a 8-12 personas reales (clientes, vendedores, almaceneros, contadores) que usen el sistema y respondan 10 preguntas de 0 a 100. Si sacamos más de 68, estamos por encima del promedio y es usable.", size=11)
h("15.6 ¿Qué más podríamos probar?",2)
p("Pruebas de carga: cuántas personas pueden entrar a la vez al catálogo o al checkout (con ApacheBench o JMeter). Pruebas de seguridad: pasarle un escáner como OWASP ZAP para buscar inyecciones, XSS o CSRF. Y pruebas de regresión: cada vez que agregas algo, volver a correr todo para asegurarte que no rompiste nada. Todo esto está en docs/Planificacion de Pruebas.docx.", size=11)

# 16
h("16. Medidas de seguridad que ya tiene el sistema (ContenidoEntregableFinal + MADEJA/UCI/OWASP)",1)
p("Aquí explico en lenguaje natural qué candados ya tiene CCOLCAS, usando los 5 pilares de MADEJA y los requisitos UCI (RNFS 1-37) y el Top 10 de OWASP. En corto: disponibilidad, autenticidad, integridad, confidencialidad y trazabilidad.", size=11, italic=True)
tbl(["¿Qué cuidamos? (Pilar)","¿Qué hace CCOLCAS? En simple","¿Dónde lo ves en el código?"],[
["Autenticidad (¿eres tú?)\nUCI 27-37","Contraseñas no se guardan como texto, sino con hash seguro (Bcrypt 12). Login verifica con Hash::check. Sin usuarios por defecto. Formularios con @csrf.","Usuario.php casts hashed, LoginController, .env BCRYPT_ROUNDS=12"],
["Autorización (¿puedes entrar?)\nRBAC","Un guardián (RoleMiddleware alias 'role') revisa tu rol antes de dejarte pasar. Cliente no entra a /admin (403).","bootstrap/app.php, routes/web.php role:..."],
["Integridad (¿nadie tocó los datos?)\nUCI 1-7","Validamos todo lo que entra, Blade escapa HTML (anti-XSS), @csrf (anti-CSRF), paginación con límite.","ValidateData, {{ }}, @csrf, paginate()"],
["Confidencialidad (¿nadie ve lo que no debe?)\nUCI 8-14","No mostramos IDs internos sensibles, mensajes de error genéricos, no guardamos datos de más, caché de datos sensibles desactivado.","403 genérico, APP_DEBUG=false en producción"],
["Trazabilidad / No repudio\nUCI 24-26","Todo queda anotado: AuditObserver escribe en bitacora_cambios (quién, qué tabla, qué hizo, datos antes/después, cuándo) y cada pedido tiene su historial.","AuditObserver.php, AppServiceProvider, historial_pedido"],
["Disponibilidad (¿siempre disponible?)\nUCI 15-23","Verificamos stock con lock para que dos personas no vendan lo mismo a la vez, todo en transacciones.","PedidoController@store, InventarioController"]])
h("16.1 ¿Y el Top 10 de OWASP?",2)
p("OWASP es una lista de los 10 errores más comunes que hacen hackeable una web. Aquí te digo cómo CCOLCAS los evita, en palabras simples:", size=11)
tbl(["Error común OWASP","¿Cómo lo evita CCOLCAS?"],[
["1. Inyección (meter código malicioso en SQL)","Usa Eloquent, que ya limpia y parametriza. No usamos SQL crudo."],
["2. XSS (meter scripts)","Blade escapa todo con {{ }} y validamos con listas blancas."],
["3. Pérdida de sesión","Sesión segura, Bcrypt 12, al cerrar sesión se borra."],
["4. Referencia directa insegura","Revisa que el objeto exista y que tu rol pueda verlo."],
["5. CSRF (hacer que hagas clic sin querer)","Todos los formularios llevan @csrf."],
["6. Configuración mala",".env con BCRYPT 12 y en producción APP_DEBUG=false."],
["7. Guardar contraseñas mal","Solo hash Bcrypt, nunca texto plano."],
["8. Dejar URLs sin proteger","Middleware role en cada ruta admin."],
["9. Comunicación insegura","Listo para HTTPS (ver contramedidas §17)."],
["10. Redirecciones tramposas","Solo redirige a route() internas, no a lo que te mande un link."]])
h("16.2 ¿Y el CMS? (INCIBE)",2)
p("INCIBE habla mucho de WordPress/Joomla/Drupal y sus cuidados (borrar instalación, cambiar prefijo, CAPTCHA). CCOLCAS no usa CMS, así que nos ahorramos esos problemas. Usamos código propio con Laravel, que es auditado y seguro. Si a futuro integramos pasarela de pago o facturación, pediremos que cumplan OWASP y privacidad por diseño.", size=11)

# 17
h("17. Contramedidas — ¿Qué más le pondríamos para estar aún más seguros? (ContenidoEntregableFinal)",1)
p("Estas son mejoras que aún no están, pero que recomendamos para cuando la página salga a internet de verdad. Las ordenamos por impacto y esfuerzo, según INCIBE (Básico/Avanzado) y MADEJA/UCI:", size=11)
tbl(["¿Qué haríamos?","Nivel INCIBE","¿Cómo se hace en CCOLCAS? Explicado fácil"],[
["Poner HTTPS con certificado (Let's Encrypt)","Básico","Cambiar APP_URL a https, forzar candado y HSTS. Así nadie ve tus contraseñas en el camino."],
["Doble factor (2FA) para admin/gerente","Avanzado","Pedir un código del celular además de la contraseña, con Fortify/Google2FA."],
["Limitar intentos + CAPTCHA en login","Básico","Si fallas mucho, te bloquea un rato y te pide 'no soy robot' (throttle + ReCAPTCHA)."],
["Cabeceras seguras (CSP, HSTS, etc.)","Básico","Un middleware que pone cascos de seguridad a cada respuesta."],
["Copias de seguridad + firewall","Básico/Avanzado","Copia diaria de la BD y un firewall/IDS que vigila intrusos."],
["Contraseñas más exigentes (12+, rotación, retardo)","Básico","Pedir 12 caracteres, cambiar cada 90 días y esperar un poco si fallas."],
["Cerrar sesión solo por inactividad (15 min)","Básico","Si te olvidas abierto, se cierra solo (SESSION_LIFETIME 15)."],
["Logs + IDS + antivirus","Avanzado","Guardar logs y tener un detector de intrusos y antivirus en el servidor."],
["Sello de confianza + auditoría + SLA","Avanzado","Que un externo revise la seguridad y te dé un sello, con contrato SLA si usas hosting externo."]])
p("Recomendación práctica: antes de salir a producción, haz lo básico urgente: HTTPS + limitar intentos + cabeceras + copias + cierre por inactividad. Lo avanzado (2FA y auditoría) puede ir en el siguiente incremento.", size=11, italic=True, color="0F172A")

# 18
h("18. Conclusiones — ¿Qué logramos? (ContenidoEntregableFinal)",1)
p("1) CCOLCAS ya centraliza todo: productos, precios, inventario, pedidos y ventas con su catálogo online. Cumple los 8 módulos A–H y el alcance de la primera versión (sin pagos ni facturación electrónica, como pedía PA1). Dejamos atrás los cuadernos, Excels y el WhatsApp desordenado.", bullet=True, size=11, after=3)
p("2) No es una app de celular (no es nativa ni híbrida móvil), pero sí es un sistema híbrido en arquitectura DHARMA: junta piezas gratis (Laravel/MySQL), servicios web y código hecho a medida. Eso justifica haberlo hecho por partes y con capas bien separadas (OOHDM).", bullet=True, size=11, after=3)
p("3) La arquitectura MVC + control por rol + auditoría automática (Observer + historial) + flujo de pedidos por rol demuestra que está bien separado, es auditable (ISO 25010) y seguro (MADEJA/UCI). El flujo 100% web — cliente confirma y ve su barrita, cada trabajador avanza donde le toca — cumple exactamente lo que CCOLCAS necesitaba.", bullet=True, size=11, after=3)
p("4) Lo probamos bien: 168 pruebas unitarias + 21 de integración + pruebas funcionales de reglas + revisión heurística + encuesta SUS propuesta. Validamos lo crítico (stock, precios por rol, descuento al vender) y la facilidad de uso. El Top 10 de OWASP está cubierto, y con las contramedidas (HTTPS, 2FA, límite de intentos) queda listo para salir a producción seguro.", bullet=True, size=11, after=3)
p("5) Cumple todo lo que pedía tu guía: nombre, descripción, producto, arquitectura, plan de 4 semanas, casos de uso, clases, navegación, secuencias, 12 pantallas con fotos fechadas, metodología/estrategia/calidad, pruebas, seguridad y anexos con código y evidencias.", bullet=True, size=11, after=3)

# 19
h("19. Referencias bibliográficas",1)
refs=["DHARMA — Discovering Hybrid Architectures by Modelling Actors (modelo i* SD/SR, ETAPATELECOM S.A.). Base para §4.2.",
"MADEJA — Junta de Andalucía. Conceptos de Seguridad en Aplicaciones Web (5 pilares, BASIC/DIGEST/FORM/CLIENT-CERT, <security-constraint>).",
"UCI (Cuba). Requisitos de Seguridad para Aplicaciones Web — RNFS 1–37 (CMMI, ISO 27001/27002/25010, OWASP).",
"Importancia de la Seguridad de los SI frente a abuso, error y hurto — Vulnerabilidades, hacking, spoofing, sniffing, Firewalls/IDS/Antivirus.",
"INCIBE. Protección de la Página Web: Políticas PYME — Checklist Básico/Avanzado, HTTPS, RGPD, CMS, logging, DoS, sellos/auditorías.",
"ISO/IEC 25010 SQuaRE — Calidad del software (4 pilares).",
"OWASP Top 10 2021 — Los 10 riesgos más comunes.",
"Laravel 12 Docs — MVC, Eloquent, Blade, Vite, Auth.",
"PA1_Suble_Lima (3).docx y PA3_Suble_Lima (2).docx — Base del proyecto CCOLCAS."]
for i,r in enumerate(refs,1):
    q=doc.add_paragraph(); rr=q.add_run(f"[{i}] {r}"); rr.font.name='Arial'; rr.font.size=Pt(8); rr.font.color.rgb=RGBColor(0x47,0x56,0x6B); q.paragraph_format.space_after=Pt(2)

# 20 ANEXOS
h("20. Anexos — Lo que tu profesor pide al final (ContenidoEntregableFinal)",1)
p("¡Ojo! Todos los anexos deben tener captura con fecha y hora visible (barra de tareas o comando date). Si no se ve la hora, no cuenta.", bold=True, size=11, color="DC2626")

h("Anexo A — Código de cada módulo (fragmentos clave)",2)
p("Te dejo los pedacitos más importantes de cada módulo. El código completo está en GitHub. Si quieres, copia y pega tal cual de tu VS Code y toma captura con la hora.", size=10, italic=True)

code("A.1 Productos — ¿Cómo calcula el precio?", """// Producto.php
public function calcularPrecioVenta(): float {
  $costoTotal = $this->costo_compra + $this->costo_flete;
  return round($costoTotal * (1 + $this->margen_ganancia / 100), 2);
}
// Ejemplo: costo 10 + flete 2 = 12, margen 20% => 12 * 1.20 = 14.40""")

code("A.2 Inventario — Mover entre almacenes", """// InventarioController@transferir
DB::transaction(function() use($data){
  $origen = StockPorAlmacen::where(...)->lockForUpdate()->first();
  if($data['cantidad'] > $origen->cantidad) throw new Exception("Stock insuficiente");
  $origen->decrement('cantidad', $data['cantidad']);
  $destino = StockPorAlmacen::firstOrCreate([...], ['cantidad'=>0]);
  $destino->increment('cantidad', $data['cantidad']);
  // Crea 2 movimientos: Salida por Transferencia y Entrada por Transferencia
});""")

code("A.3 Pedidos — ¿Quién puede mover el pedido? (Workflow por rol)", """// PedidoWorkflowService
'Pendiente' => ['Confirmado','Cancelado'],
'Confirmado' => ['En Almacén','Cancelado'], // Reglas
// ROLE_FOR_TARGET['Confirmado'] = ['Vendedor','Administrador','Gerente','Subgerente']
public static function puedeTransitar(Pedido $p, string $dest, string $rol): bool { ... }
PedidoWorkflowService::transitar($pedido, $dest->nombre, Auth::id());
HistorialPedido::create([... estado anterior/nuevo, usuario_id, observacion]);""")

code("A.4 Auditoría — El anotador automático", """// AuditObserver: cuando creas/editas/borras Producto/Pedido/Venta
BitacoraCambio::create(['datos_previos'=>json, 'datos_nuevos'=>json, 'usuario_id'=>Auth::id()])
// AppServiceProvider::boot(): Producto::observe(AuditObserver::class); Pedido::observe(...); Venta::observe(...);""")

code("A.5 Seguridad — El guardián de roles", """// bootstrap/app.php -> alias ['role' => RoleMiddleware::class]
// routes/web.php -> prefix('admin')->middleware('role:Administrador,Gerente,...')
// RoleMiddleware: if(in_array($rol->nombre, $roles)) deja pasar, si no 403""")

code("A.6 Ventas — ¿Cómo descuenta stock al vender?", """DB::transaction(function() use($data,$pedido){
  $venta = Venta::create([...]);
  foreach($pedido->detalles as $d){
    $stock = StockPorAlmacen::where('producto_id',$d->producto_id)->lockForUpdate()->first();
    $stock?->decrement('cantidad',$d->cantidad);
    MovimientoInventario::create([... 'Salida por Venta' ...]);
  }
});""")

code("A.7 Checkout — ¿Cómo crea el pedido sin trampa?", """DB::transaction(function() use($request,$cliente,$carrito){
  // Vuelve a leer precio y stock reales de la BD (por si el carrito fue manipulado)
  $productos = Producto::with('stocks')->whereIn('id', array_keys($carrito))->get();
  // Verifica stock, crea Pedido + DetallePedido + HistorialPedido inicial
});""")

h("Anexo B — Código de conexión a la base de datos",2)
p("En palabras simples: es el archivo que dice dónde está la base de datos y con qué clave se entra.", size=10)
code("B.1 Archivo .env — La llave de la BD", """APP_NAME=CCOLCAS
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ccolcasdb
DB_USERNAME=root
DB_PASSWORD=
BCRYPT_ROUNDS=12
SESSION_DRIVER=file""")
code("B.2 config/database.php + Migración — Las 19 tablas", """// config/database.php: 'mysql' => ['host'=>env('DB_HOST'), 'database'=>env('DB_DATABASE','ccolcasdb')]
// Migración 0001_... crea: productos, categorías, almacenes, stock_por_almacen, roles, usuarios, clientes, etc.
// Para crear todo: php artisan migrate --force && php artisan db:seed --force""")
code("B.3 config/auth.php — ¿Qué tabla guarda usuarios?", """'providers'=>['users'=>['driver'=>'eloquent','model'=>App\\Models\\Usuario::class]] // usa tabla usuarios""")
cap("31", "Anexo B — phpMyAdmin con la BD", "phpMyAdmin en http://localhost/phpmyadmin mostrando ccolcasdb y sus 19 tablas", "Abre phpMyAdmin y captura con el reloj visible")

h("Anexo C — Capturas de pruebas por módulo (con fecha y hora)",2)
p("Tu guía dice textual: 'Dichas capturas deben estar tomadas con la fecha y hora de su computador.' La forma más fácil: abre la terminal y el navegador uno al lado del otro, ejecuta el comando y haz Win+Shift+S incluyendo la barra de tareas con el reloj.", bold=True, size=10, color="DC2626")
tbl(["Anexo","¿Qué prueba? En simple","Comando / Ruta donde tomarla"],[
["C.1","Catálogo — que solo se ven productos activos, búsqueda y filtro","/, /catalogo/buscar + php artisan test --filter=CatalogoFeature"],
["C.2","Carrito — agregar, cambiar cantidad, stock insuficiente","/carrito + POST /carrito/agregar"],
["C.3","Login/Registro — entrar bien/mal, rol cliente vs personal","Modal login en /"],
["C.4","Checkout → Pedido — crear pedido y ver en Mis pedidos","/checkout → /mi-cuenta/pedidos"],
["C.5","Inventario — movimiento, ajuste y transferencia","/admin/inventario"],
["C.6","Productos/Precios — crear con fórmula y desactivar","/admin/productos"],
["C.7","Pedidos workflow — mover estados por rol","/admin/pedidos/{id}"],
["C.8","Ventas — registrar y descontar stock","/admin/ventas"],
["C.9","Permisos — cliente no entra a admin","/admin/dashboard con usuario cliente (403)"],
["C.10","Todo — 168 tests + build","php artisan test + npm run build"]])
for num, tit in [("C.1","Catálogo"),("C.2","Carrito"),("C.3","Login/Registro"),("C.4","Checkout → Pedido"),("C.5","Inventario"),("C.6","Productos/Precios"),("C.7","Pedidos workflow"),("C.8","Ventas"),("C.9","RBAC 403"),("C.10","Tests globales (168) + build")]:
    cap(num, tit, "Ver tabla de arriba", "Captura con fecha/hora — pega aquí")

pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER; pp.paragraph_format.space_before=Pt(16)
r=pp.add_run("— Fin del Entregable Final —  CCOLCAS  •  Suble Lima Denilson Rivaldo  •  Ingeniería Web  •  Sede Cusco 2026 —"); r.bold=True; r.font.name='Arial'; r.font.size=Pt(9); r.font.color.rgb=RGBColor(0x0F,0x17,0x2A)
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER
r=pp.add_run("¿Listo para entregar? Reemplaza todas las cajitas 📸 con tus fotos reales, guarda como: Suble_Lima_Denilson_Rivaldo_EntregableFinal.docx  •  Arial 12  •  Páginas numeradas"); r.font.name='Arial'; r.font.size=Pt(8); r.italic=True; r.font.color.rgb=RGBColor(0xDC,0x26,0x26)

out = r"docs/IngWebEntregabes/EntregableFinal_Suble_Lima_Denilson_Rivaldo.docx"
doc.save(out)
print(f"Guardado en {out} — {doc.core_properties}")

