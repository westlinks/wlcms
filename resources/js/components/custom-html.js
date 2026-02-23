// Custom HTML extension for TipTap
// Preserves divs and all HTML attributes including classes

import { Node } from '@tiptap/core'

export const CustomDiv = Node.create({
  name: 'customDiv',
  
  group: 'block',
  
  content: 'block*',
  
  parseHTML() {
    return [
      {
        tag: 'div',
        // Strip all attributes we don't want
        getAttrs: () => ({}),
      },
    ]
  },
  
  renderHTML() {
    // Render as plain <div> with NO attributes
    return ['div', 0]
  },
  
  addAttributes() {
    // Return empty object - we don't want ANY attributes preserved
    return {}
  },
})

// Custom paragraph extension - STRIPS all unwanted attributes
export const CustomParagraph = Node.create({
  name: 'paragraph',
  
  priority: 1000,
  
  group: 'block',
  
  content: 'inline*',
  
  parseHTML() {
    return [{ 
      tag: 'p',
      // Strip all attributes we don't want
      getAttrs: () => ({}),
    }]
  },
  
  renderHTML() {
    // Render as plain <p> with NO attributes
    return ['p', 0]
  },
  
  addAttributes() {
    // Return empty object - we don't want ANY attributes preserved
    return {}
  },
})

// Custom anchor/link extension that preserves all attributes
export const CustomLink = Node.create({
  name: 'customLink',
  
  priority: 1000,
  
  inline: true,
  
  group: 'inline',
  
  content: 'text*',
  
  parseHTML() {
    return [{ tag: 'a[href]' }]
  },
  
  renderHTML({ HTMLAttributes }) {
    return ['a', HTMLAttributes, 0]
  },
  
  addAttributes() {
    return {
      href: {
        default: null,
      },
      target: {
        default: null,
      },
      rel: {
        default: null,
      },
      class: {
        default: null,
        parseHTML: element => element.getAttribute('class'),
        renderHTML: attributes => {
          if (!attributes.class) return {}
          return { class: attributes.class }
        },
      },
    }
  },
})

// Custom SVG extension - Preserves inline SVG with all attributes
export const CustomSVG = Node.create({
  name: 'customSvg',
  
  group: 'inline',
  
  inline: true,
  
  atom: true, // Prevent TipTap from parsing SVG internals
  
  parseHTML() {
    return [
      {
        tag: 'svg',
        getAttrs: dom => {
          // Capture ALL attributes from the SVG element
          const attrs = {};
          for (let i = 0; i < dom.attributes.length; i++) {
            const attr = dom.attributes[i];
            attrs[attr.name] = attr.value;
          }
          // Preserve inner HTML as raw content
          attrs.innerHTML = dom.innerHTML;
          return attrs;
        },
      },
    ]
  },
  
  renderHTML({ HTMLAttributes }) {
    // Extract innerHTML and other attributes
    const innerHTML = HTMLAttributes.innerHTML || '';
    const attrs = { ...HTMLAttributes };
    delete attrs.innerHTML;
    
    return ['svg', attrs, 0]; // Note: TipTap will handle innerHTML via atom
  },
  
  addAttributes() {
    return {
      // Standard SVG attributes
      width: { default: null },
      height: { default: null },
      viewBox: { default: null },
      xmlns: { default: 'http://www.w3.org/2000/svg' },
      fill: { default: null },
      stroke: { default: null },
      'stroke-width': { default: null },
      'stroke-linecap': { default: null },
      'stroke-linejoin': { default: null },
      preserveAspectRatio: { default: null },
      
      // Positioning
      x: { default: null },
      y: { default: null },
      
      // Styling
      class: { default: null },
      style: { default: null },
      
      // Capture innerHTML separately (SVG content)
      innerHTML: { default: '' },
      
      // Catch-all for any other SVG attributes
      // This allows custom data attributes, aria attributes, etc.
    }
  },
  
  addNodeView() {
    return ({ node }) => {
      const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
      
      // Apply all attributes
      Object.entries(node.attrs).forEach(([key, value]) => {
        if (key !== 'innerHTML' && value !== null && value !== undefined) {
          svg.setAttribute(key, value);
        }
      });
      
      // Set inner HTML (paths, circles, etc.)
      if (node.attrs.innerHTML) {
        svg.innerHTML = node.attrs.innerHTML;
      }
      
      return {
        dom: svg,
      };
    };
  },
})

// Also add SVG child elements (path, circle, rect, etc.)
export const CustomSVGPath = Node.create({
  name: 'svgPath',
  
  group: 'block',
  
  atom: true,
  
  parseHTML() {
    return [{ tag: 'path' }]
  },
  
  renderHTML({ HTMLAttributes }) {
    return ['path', HTMLAttributes]
  },
  
  addAttributes() {
    return {
      d: { default: null },
      fill: { default: null },
      stroke: { default: null },
      'stroke-width': { default: null },
      'stroke-linecap': { default: null },
      'stroke-linejoin': { default: null },
      'fill-rule': { default: null },
      'clip-rule': { default: null },
    }
  },
})
