import {jest} from '@jest/globals'

jest.spyOn(console, 'error').mockImplementation(msg => {})
