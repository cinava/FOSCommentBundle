<?php

/*
 * This file is part of the FOSCommentBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace FOS\CommentBundle\Tests\Acl;

use FOS\CommentBundle\Acl\AclThreadManager;
use PHPUnit\Framework\TestCase;

/**
 * Tests the functionality provided by Acl\AclThreadManager.
 *
 * @author Tim Nagel <tim@nagel.com.au>
 */
class AclThreadManagerTest extends TestCase
{
    protected $realManager;
    protected $threadSecurity;
    protected $thread;

    public function setUp(): void
    {
        $this->realManager = $this->getMockBuilder('FOS\CommentBundle\Model\ThreadManagerInterface')->getMock();
        $this->threadSecurity = $this->getMockBuilder('FOS\CommentBundle\Acl\ThreadAclInterface')->getMock();
        $this->thread = $this->getMockBuilder('FOS\CommentBundle\Model\ThreadInterface')->getMock();
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testFindThreadById()
    {
        $threadId = 'hello';
        $this->realManager->expects($this->once())
            ->method('findThreadById')
            ->with($threadId)
            ->will($this->returnValue($this->thread));

        $this->threadSecurity->expects($this->once())
            ->method('canView')
            ->with($this->thread)
            ->will($this->returnValue(false));

        $manager = new AclThreadManager($this->realManager, $this->threadSecurity);
        $manager->findThreadById($threadId);
    }

    public function testFindThreadByIdNotFound()
    {
        $threadId = 'hello';
        $this->realManager->expects($this->once())
            ->method('findThreadById')
            ->with($threadId)
            ->will($this->returnValue(null));

        $this->threadSecurity->expects($this->never())
            ->method('canView');

        $manager = new AclThreadManager($this->realManager, $this->threadSecurity);
        $this->assertNull($manager->findThreadById($threadId));
    }

    // findThreadBy - permission denied, can result in null, what to do about invalid criteria

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testFindThreadBy()
    {
        $conditions = ['id' => 123];
        $expectedResult = $this->thread;

        $this->realManager->expects($this->once())
            ->method('findThreadBy')
            ->with($conditions)
            ->will($this->returnValue($expectedResult));

        $this->threadSecurity->expects($this->once())
            ->method('canView')
            ->with($this->thread)
            ->will($this->returnValue(false));

        $manager = new AclThreadManager($this->realManager, $this->threadSecurity);
        $manager->findThreadBy($conditions);
    }

    public function testFindThreadByNoResult()
    {
        $conditions = ['id' => 123];
        $expectedResult = null;

        $this->realManager->expects($this->once())
            ->method('findThreadBy')
            ->with($conditions)
            ->will($this->returnValue($expectedResult));

        $this->threadSecurity->expects($this->never())
            ->method('canView');

        $manager = new AclThreadManager($this->realManager, $this->threadSecurity);
        $this->assertNull($manager->findThreadBy($conditions));
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testFindAllThreads()
    {
        $expectedResult = [$this->thread];

        $this->realManager->expects($this->once())
            ->method('findAllThreads')
            ->will($this->returnValue($expectedResult));

        $this->threadSecurity->expects($this->once())
            ->method('canView')
            ->with($this->thread)
            ->will($this->returnValue(false));

        $manager = new AclThreadManager($this->realManager, $this->threadSecurity);
        $manager->findAllThreads();
    }

    public function testFindAllThreadsCanView()
    {
        $expectedResult = [$this->thread];

        $this->realManager->expects($this->once())
            ->method('findAllThreads')
            ->will($this->returnValue($expectedResult));

        $this->threadSecurity->expects($this->once())
            ->method('canView')
            ->with($this->thread)
            ->will($this->returnValue(true));

        $manager = new AclThreadManager($this->realManager, $this->threadSecurity);
        $result = $manager->findAllThreads();

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testAddThread()
    {
        $this->realManager->expects($this->never())
            ->method('saveThread');

        $this->threadSecurity->expects($this->once())
            ->method('canCreate')
            ->will($this->returnValue(false));

        $manager = new AclThreadManager($this->realManager, $this->threadSecurity);
        $manager->saveThread($this->thread);
    }

    public function testAddThreadCanCreate()
    {
        $this->threadSecurity->expects($this->once())
                ->method('canCreate')
                ->will($this->returnValue(true));

        $this->realManager->expects($this->once())
                ->method('saveThread')
                ->with($this->thread);

        $this->realManager->expects($this->once())
                ->method('isNewThread')
                ->with($this->thread)
                ->will($this->returnValue(true));

        $manager = new AclThreadManager($this->realManager, $this->threadSecurity);
        $manager->saveThread($this->thread);
    }

    public function testCreateThread()
    {
        $this->realManager->expects($this->once())
            ->method('createThread')
            ->will($this->returnValue($this->thread));

        $manager = new AclThreadManager($this->realManager, $this->threadSecurity);
        $result = $manager->createThread();

        $this->assertSame($this->thread, $result);
    }

    public function testGetClass()
    {
        $expectedResult = 'Test\\Class';

        $this->realManager->expects($this->once())
            ->method('getClass')
            ->will($this->returnValue($expectedResult));

        $manager = new AclThreadManager($this->realManager, $this->threadSecurity);
        $result = $manager->getClass();

        $this->assertSame($expectedResult, $result);
    }
}
