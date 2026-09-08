import { Controller } from '@hotwired/stimulus'
import Sortable from 'sortablejs'

export default class extends Controller {
    static values = { saveUrl: String }

    connect() {
        this.dirty = false
        this.saveBtn = document.getElementById('btn-save')
        this.status = document.getElementById('save-status')

        this.initAllLists()

        if (this.saveBtn) {
            this.saveBtn.addEventListener('click', () => this.save())
        }
    }

    disconnect() {
        this.element.querySelectorAll('.cat-list, .cat-children').forEach(ul => {
            const instance = Sortable.get(ul)
            if (instance) instance.destroy()
        })
    }

    initAllLists() {
        this.element.querySelectorAll('.cat-list, .cat-children').forEach(ul => {
            if (!Sortable.get(ul)) {
                this.createSortable(ul)
            }
        })
    }

    createSortable(ul) {
        Sortable.create(ul, {
            group: 'cat-tree',
            animation: 180,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            fallbackOnBody: true,
            swapThreshold: 0.55,
            onEnd: () => {
                // Initialiser les nouvelles listes vides apparues après un drop
                this.initAllLists()
                this.markDirty()
            },
        })

        this.updateEmptyClass(ul)
    }

    markDirty() {
        this.dirty = true
        if (this.saveBtn) this.saveBtn.disabled = false
        this.showStatus('', '')
        this.updateAllEmptyClasses()
    }

    updateAllEmptyClasses() {
        this.element.querySelectorAll('.cat-children').forEach(ul => this.updateEmptyClass(ul))
    }

    updateEmptyClass(ul) {
        if (ul.children.length === 0) {
            ul.classList.add('sortable-empty')
        } else {
            ul.classList.remove('sortable-empty')
        }
    }

    buildHierarchy(ul, parentId) {
        const items = []
        for (const li of ul.children) {
            if (!li.dataset.id) continue
            const id = parseInt(li.dataset.id, 10)
            items.push({ id, parentId })
            const childUl = li.querySelector(':scope > .cat-children')
            if (childUl) {
                items.push(...this.buildHierarchy(childUl, id))
            }
        }
        return items
    }

    async save() {
        if (!this.dirty) return

        const rootUl = this.element.querySelector('.cat-list')
        if (!rootUl) return

        const hierarchy = this.buildHierarchy(rootUl, 0)
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? ''

        this.showStatus('Enregistrement…', 'saving')
        if (this.saveBtn) this.saveBtn.disabled = true

        try {
            const response = await fetch(this.saveUrlValue, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrfToken,
                },
                body: JSON.stringify(hierarchy),
            })

            if (!response.ok) {
                const data = await response.json().catch(() => ({}))
                throw new Error(data.error ?? `Erreur HTTP ${response.status}`)
            }

            this.dirty = false
            this.showStatus('Enregistré', 'saved')
            setTimeout(() => this.showStatus('', ''), 3000)
        } catch (err) {
            this.showStatus(`Erreur : ${err.message}`, 'error')
            if (this.saveBtn) this.saveBtn.disabled = false
        }
    }

    showStatus(message, type) {
        if (!this.status) return
        this.status.textContent = message
        this.status.className = 'ct-status'
        if (type) this.status.classList.add(type)
    }
}
