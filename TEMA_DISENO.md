# 🎨 Tema de Diseño Profesional - Sistema de Gestión de Producción

## Paleta de Colores

### Colores Principales
- **Verde Esmeralda** (#10b981): Color primario profesional que transmite crecimiento, productividad y confianza
- **Ámbar/Naranja** (#f59e0b): Color secundario cálido para acentos y alertas importantes
- **Grafito Oscuro** (#111827 - #1f2937): Fondos principales que proporcionan elegancia y modernidad

### Colores Semánticos
- **Éxito**: Verde esmeralda (#10b981)
- **Advertencia**: Ámbar (#f59e0b)
- **Peligro**: Rojo (#ef4444)
- **Información**: Cian (#06b6d4)

## Características del Diseño

### 1. **Tema Oscuro Profesional**
- Fondo degradado con tonos de grafito
- Efectos sutiles de iluminación radial con los colores de marca
- Contraste óptimo para reducir fatiga visual

### 2. **Elementos Interactivos**
- **Tarjetas (Cards)**: 
  - Efecto hover con elevación suave
  - Borde superior animado con el color primario
  - Sombras profundas para profundidad visual
  
- **Botones**:
  - Degradados suaves
  - Efecto de brillo deslizante al pasar el mouse
  - Elevación al hacer hover
  - Sombras de resplandor según el color

- **Navbar**:
  - Borde superior con degradado esmeralda
  - Backdrop blur para efecto glassmorphism
  - Marca del sistema con resplandor verde

### 3. **Tipografía**
- **Fuente**: Inter (Google Fonts)
- **Pesos**: 300 (Light), 400 (Regular), 500 (Medium), 600 (Semi-bold), 700 (Bold)
- Espaciado de letras optimizado para legibilidad
- Sombras sutiles en títulos principales

### 4. **Tablas**
- Encabezados con el color primario
- Hover row con tinte verde suave
- Bordes discretos para separación visual

### 5. **Formularios**
- Inputs con fondo semi-transparente
- Focus state con borde verde y sombra de resplandor
- Labels en color mutado para jerarquía visual

### 6. **Animaciones**
- Transiciones suaves (cubic-bezier)
- Fade-in para elementos al cargar
- Efectos de elevación y desplazamiento

## Scrollbar Personalizado
- Degradado verde esmeralda
- Fondo oscuro
- Efecto hover más claro

## Ventajas del Nuevo Tema

✅ **Profesional**: Paleta de colores moderna y empresarial  
✅ **Sin Azul Excesivo**: Uso de verde esmeralda como color primario  
✅ **Legible**: Alto contraste optimizado para tema oscuro  
✅ **Consistente**: Variables CSS para fácil mantenimiento  
✅ **Moderno**: Efectos glassmorphism y degradados sutiles  
✅ **Responsive**: Adaptado para dispositivos móviles  
✅ **Accesible**: Colores con contraste WCAG AA  

## Personalización Rápida

Para cambiar colores principales, edita las variables en `public/css/custom.css`:

```css
:root {
    --primary-color: #10b981;
    --secondary-color: #f59e0b;
    --dark-bg: #111827;
    --card-bg: #1f2937;
}
```

---

**Fecha de implementación**: 15 de enero, 2026  
**Versión**: 2.0 - Tema Profesional Esmeralda
