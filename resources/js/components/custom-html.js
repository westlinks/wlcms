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
