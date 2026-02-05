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
        getAttrs: (node) => {
          // Preserve all attributes
          const attrs = {};
          if (node.hasAttributes()) {
            for (let i = 0; i < node.attributes.length; i++) {
              const attr = node.attributes[i];
              attrs[attr.name] = attr.value;
            }
          }
          return attrs.class || attrs.id || Object.keys(attrs).length > 0 ? attrs : false;
        },
      },
    ]
  },
  
  renderHTML({ HTMLAttributes }) {
    return ['div', HTMLAttributes, 0]
  },
  
  addAttributes() {
    return {
      class: {
        default: null,
        parseHTML: element => element.getAttribute('class'),
        renderHTML: attributes => {
          if (!attributes.class) {
            return {}
          }
          return { class: attributes.class }
        },
      },
      id: {
        default: null,
      },
      style: {
        default: null,
      },
      // Catch-all for any other attributes
      ...Object.fromEntries(
        ['data-', 'aria-', 'role', 'tabindex'].map(prefix => [
          prefix,
          {
            default: null,
            parseHTML: element => {
              const attrs = {};
              for (let i = 0; i < element.attributes.length; i++) {
                const attr = element.attributes[i];
                if (attr.name.startsWith(prefix) || attr.name === prefix.replace('-', '')) {
                  attrs[attr.name] = attr.value;
                }
              }
              return Object.keys(attrs).length > 0 ? attrs : null;
            },
          },
        ])
      ),
    }
  },
})

// Custom paragraph extension that preserves classes
export const CustomParagraph = Node.create({
  name: 'paragraph',
  
  priority: 1000,
  
  group: 'block',
  
  content: 'inline*',
  
  parseHTML() {
    return [{ tag: 'p' }]
  },
  
  renderHTML({ HTMLAttributes }) {
    return ['p', HTMLAttributes, 0]
  },
  
  addAttributes() {
    return {
      class: {
        default: null,
        parseHTML: element => element.getAttribute('class'),
        renderHTML: attributes => {
          if (!attributes.class) {
            return {}
          }
          return { class: attributes.class }
        },
      },
    }
  },
})
