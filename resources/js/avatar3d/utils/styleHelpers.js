/**
 * Style helper utilities for Avatar 3D
 */

/**
 * Format position value - adds 'px' if value is numeric only
 * @param {string|number} val - Position value
 * @returns {string} Formatted position with unit
 */
export function formatPosition(val) {
  if (!val || val === '0') return '0px';
  if (/^\d+$/.test(val)) return `${val}px`;
  return val;
}

/**
 * Build container style object
 * @param {Object} options - Style options
 * @returns {Object} CSS style object
 */
export function buildContainerStyle({
  height,
  aspectRatio,
  fixedPosition,
  positionBottom,
  positionRight,
  transparentBackground,
}) {
  return {
    height: height,
    width: `calc(${height} * ${aspectRatio})`,
    maxWidth: '100%',
    overflow: 'hidden',
    ...(fixedPosition && {
      position: 'fixed',
      bottom: formatPosition(positionBottom),
      right: formatPosition(positionRight),
      zIndex: 1000,
    }),
    background: transparentBackground ? 'transparent' : undefined,
  };
}

/**
 * Build container CSS classes
 * @param {Object} options - Class options
 * @returns {string} CSS class string
 */
export function buildContainerClasses({ fixedPosition, transparentBackground, widgetMode }) {
  const classes = ['avatar3d-container'];
  if (fixedPosition) classes.push('avatar3d-fixed');
  if (transparentBackground) classes.push('avatar3d-transparent');
  if (widgetMode) classes.push('avatar3d-widget');
  return classes.join(' ');
}
