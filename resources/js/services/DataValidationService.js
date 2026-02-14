// DataValidationService.js: Validate zone data for WLCMS content zones
export default class DataValidationService {
  static validateZone(zone, value) {
    switch (zone.type) {
      case 'rich_text':
        return typeof value === 'string';
      case 'conditional':
        return typeof value === 'string';
      case 'repeater':
        return Array.isArray(value) && value.every(item => item.title && item.text);
      case 'media_gallery':
        return Array.isArray(value) && value.every(item => item.url);
      case 'file_list':
        return Array.isArray(value) && value.every(file => file.url && file.name);
      case 'link_list':
        return Array.isArray(value) && value.every(link => link.label && link.url);
      case 'form_embed':
        return value && value.type && (value.formId || value.embedCode);
      default:
        return false;
    }
  }

  static validateAll(zones, zoneData) {
    return zones.every(zone => {
      if (zone.required) {
        return this.validateZone(zone, zoneData[zone.key]);
      }
      return true;
    });
  }
}
