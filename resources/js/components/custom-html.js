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
        // Accept all divs
        getAttrs: () => null,
      },
    ]
  },
  
  renderHTML({ HTMLAttributes }) {
    return ['div', HTMLAttributes, 0]
  },
  
  addAttributes() {
    return {
      // Dynamically preserve all attributes
      class: {
        default: null,
        parseHTML: element => element.getAttribute('class'),
        renderHTML: attributes => {
          if (!attributes.class) return {}
          return { class: attributes.class }
        },
      },
      id: {
        default: null,
        parseHTML: element => element.getAttribute('id'),
        renderHTML: attributes => {
          if (!attributes.id) return {}
          return { id: attributes.id }
        },
      },
      style: {
        default: null,
        parseHTML: element => element.getAttribute('style'),
        renderHTML: attributes => {
          if (!attributes.style) return {}
          return { style: attributes.style }
        },
      },
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
          if (!attributes.class) return {}
          return { class: attributes.class }
        },
      },
    }
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
