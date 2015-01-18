<?php if (!defined('APPLICATION')) exit();
echo Wrap($this->Data('Title'), 'h1');

$this->ConfigurationModule->Render();

echo Wrap(
        Wrap($this->Form->Textbox('CountUsers', array('value' => 5, 'class' => 'Hidden SmallInput')) . Anchor('Generate Users', 'plugin/datagenerator/users', array('class' => 'Hijack Button Users')), 'li') .
        Wrap($this->Form->Textbox('CountDiscussions', array('value' => 5, 'class' => 'Hidden SmallInput')) . Anchor('Generate Discussions', 'plugin/datagenerator/discussions', array('class' => 'Hijack Button Discussions')), 'li') .
        Wrap($this->Form->Textbox('CountComments', array('value' => 5, 'class' => 'Hidden SmallInput')) . Anchor('Generate Comments', 'plugin/datagenerator/comments', array('class' => 'Hijack Button Comments')), 'li'), 'ul', array('class' => 'DataGenButtons'));
