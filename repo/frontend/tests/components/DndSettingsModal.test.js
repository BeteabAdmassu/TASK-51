import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import DndSettingsModal from '@/components/chat/DndSettingsModal.vue'

describe('DndSettingsModal', () => {
  it('emits save when dnd settings are updated', async () => {
    const wrapper = mount(DndSettingsModal, {
      props: {
        modelValue: true,
        start: '22:00',
        end: '07:00',
      },
      global: {
        stubs: { Teleport: true },
      },
    })

    const inputs = wrapper.findAll('input')
    await inputs[0].setValue('21:00')
    await inputs[1].setValue('06:00')

    await wrapper.findAll('button').find((button) => button.text().includes('Save')).trigger('click')

    const emitted = wrapper.emitted('save')
    expect(emitted).toBeTruthy()
    expect(emitted[0][0]).toEqual({ dnd_start: '21:00', dnd_end: '06:00' })
  })
})
