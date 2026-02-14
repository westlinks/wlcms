// ZoneRenderingHelpers.js: Render zone data for Blade templates
export function renderZone(zone, value) {
  switch (zone.type) {
    case 'rich_text':
      return value ? value : '';
    case 'conditional':
      return value ? value : '';
    case 'repeater':
      return Array.isArray(value)
        ? value.map(item => `<div class="feature-card"><div class="feature-icon">${item.icon}</div><h3>${item.title}</h3><p>${item.text}</p></div>`).join('')
        : '';
    case 'media_gallery':
      return Array.isArray(value)
        ? value.map(item => `<img src="${item.url}" alt="${item.alt}" class="sponsor-logo" />`).join('')
        : '';
    case 'file_list':
      return Array.isArray(value)
        ? value.map(file => `<a href="${file.url}" target="_blank">${file.name}</a><span>${file.description}</span>`).join('')
        : '';
    case 'link_list':
      return Array.isArray(value)
        ? value.map(link => `<a href="${link.url}" target="_blank">${link.label}</a>`).join('')
        : '';
    case 'form_embed':
      if (value.type === 'built-in') {
        return `<form id="${value.formId}"></form>`;
      } else if (value.type === 'embed') {
        return value.embedCode;
      }
      return '';
    default:
      return '';
  }
}

export function renderAllZones(zones, zoneData) {
  const rendered = {};
  zones.forEach(zone => {
    rendered[zone.key] = renderZone(zone, zoneData[zone.key]);
  });
  return rendered;
}
